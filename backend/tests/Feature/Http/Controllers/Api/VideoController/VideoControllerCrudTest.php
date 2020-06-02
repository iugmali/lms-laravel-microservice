<?php


namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Tests\Feature\Http\Controllers\Api\VideoController\BaseVideoControllerTestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestResources;

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);
        $resource = VideoResource::collection(collect([$this->video]));
        $this->assertResource($response, $resource);
        $this->assertIfFilesUrlExists($this->video, $response);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->serializedFields]);
        $resource = new VideoResource(Video::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
        $this->assertIfFilesUrlExists($this->video, $response);
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
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);
        $data = [
            [
            'send_data' => $this->sendData,
            'test_data' => $testData + ['opened' => false]
            ],
            [
            'send_data' => $this->sendData + ['opened' => true],
            'test_data' => $testData + ['opened' => true]
            ],
            [
            'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[2]],
            'test_data' => $testData + ['rating' => Video::RATING_LIST[2]]
            ]
        ];
        foreach ($data as $key => $value) {
            // store
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' =>  $this->serializedFields]);
            $this->assertHasCategory($this->getIdFromResponse($response), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($this->getIdFromResponse($response), $value['send_data']['genres_id'][0]);
            $resource = new VideoResource(Video::find($this->getIdFromResponse($response)));
            $this->assertResource($response, $resource);
            $this->assertIfFilesUrlExists($this->video, $response);

            // update
            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' =>  $this->serializedFields]);
            $this->assertHasCategory($this->getIdFromResponse($response), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($this->getIdFromResponse($response), $value['send_data']['genres_id'][0]);
            $resource = new VideoResource(Video::find($this->getIdFromResponse($response)));
            $this->assertResource($response, $resource);
            $this->assertIfFilesUrlExists($this->video, $response);
        }
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
        $this->assertHasCategory($this->getIdFromResponse($response), $categoriesId[0]);
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
        $response = $this->json('PUT', route('videos.update', ['video' => $this->getIdFromResponse($response)]), $sendData);
        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $this->getIdFromResponse($response)
        ]);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoriesId[1]);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoriesId[2]);
        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $this->getIdFromResponse($response)
        ]);
        $this->assertHasGenre($this->getIdFromResponse($response), $genresId[1]);
        $this->assertHasGenre($this->getIdFromResponse($response), $genresId[2]);
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
