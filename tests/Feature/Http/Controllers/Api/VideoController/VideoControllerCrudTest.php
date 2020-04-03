<?php


namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Http\Controllers\Api\VideoController\BaseVideoControllerTestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves;

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidData()
    {
        $this->assertInvalidData([
            'title' => '',
            'description' => '',
            'rating' => '',
            'year_launched' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ], 'required');
        $this->assertInvalidData(['title' => str_repeat('a', 256)], 'max.string', ['max' => 255]);
        $this->assertInvalidData(['year_launched' => 'ano'], 'date_format', ['format' => 'Y']);
        $this->assertInvalidData(['opened' => 'a'], 'boolean');
        $this->assertInvalidData(['duration' => 'a'], 'integer');
        $this->assertInvalidData(['rating' => 0], 'in');
        $this->assertInvalidData(['genres_id' => 'test'], 'array');
        $this->assertInvalidData(['categories_id' => 'test'], 'array');
        $this->assertInvalidData(['categories_id' => [12]], 'exists');
        $this->assertInvalidData(['genres_id' => [12]], 'exists');
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $category->delete();
        $genre->delete();
        $this->assertInvalidData(['categories_id' => [$category->id], 'genres_id' => [$genre->id]], 'exists');
    }

    private function assertInvalidData($data, $rule, $rules_param = [])
    {
        $this->assertInvalidStoreAction($data, $rule, $rules_param);
        $this->assertInvalidUpdateAction($data, $rule, $rules_param);
    }

    public function testSaveWithoutFiles()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $data = [
            [
            'send_data' => $this->sendData + [
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id]
                ],
            'test_data' => $this->sendData + ['opened' => false]
            ],
            [
            'send_data' => $this->sendData + [
                    'opened' => true,
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id]
                ],
            'test_data' => $this->sendData + ['opened' => true]
            ],
            [
            'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[2],
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id]
                ],
            'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[2]]
            ]
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);
            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);
        }
    }

    public function testStore()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $data = [
            [
                'send_data' => $this->sendData + [
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id]
                    ],
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                        'opened' => true,
                        'categories_id' => [$category->id],
                        'genres_id' => [$genre->id]
                    ],
                'test_data' => $this->sendData + ['opened' => true]
            ]
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $category->id);
            $this->assertHasGenre($response->json('id'), $genre->id);
        }
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $data = [
            'title' => 'teste2',
            'description' => str_repeat('a', 300),
            'year_launched' => 2014,
            'opened' => true,
            'rating' => '18',
            'duration' => 100
        ];
        $data_sent = $data + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
        ];
        $response = $this->assertUpdate($data_sent, $data);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertHasCategory($response->json('id'), $category->id);
        $this->assertHasGenre($response->json('id'), $genre->id);
    }

    public function testSyncCategoriesGenres()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $genresId = factory(Genre::class, 3)->create()->pluck('id')->toArray();
        $sendData = [
            'title' => 'teste2',
            'description' => str_repeat('a', 300),
            'year_launched' => 2014,
            'opened' => true,
            'rating' => '18',
            'duration' => 100,
            'categories_id' => [$categoriesId[0]],
            'genres_id' => [$genresId[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertHasCategory($response->json('id'), $categoriesId[0]);
        $sendData = [
            'title' => 'teste2',
            'description' => str_repeat('a', 300),
            'year_launched' => 2014,
            'opened' => true,
            'rating' => '18',
            'duration' => 100,
            'categories_id' => [$categoriesId[1],$categoriesId[2]],
            'genres_id' => [$genresId[1],$genresId[2]]
        ];
        $response = $this->json('PUT', route('videos.update', ['video' => $response->json('id')]), $sendData);
        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $response->json('id')
        ]);
        $this->assertHasCategory($response->json('id'), $categoriesId[1]);
        $this->assertHasCategory($response->json('id'), $categoriesId[2]);
        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $response->json('id')
        ]);
        $this->assertHasGenre($response->json('id'), $genresId[1]);
        $this->assertHasGenre($response->json('id'), $genresId[2]);
    }

    private function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    private function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);
    }

    public function testDestroy()
    {
        $this->video->refresh();
        $response = $this->json('DELETE', route('videos.destroy', ['video'  => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model() {
        return Video::class;
    }

}
