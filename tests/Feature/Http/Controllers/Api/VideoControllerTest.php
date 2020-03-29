<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
        $this->sendData = [
            'title' => 'teste',
            'description' => str_repeat('a', 400),
            'year_launched' => 2000,
            'rating' => '12',
            'duration' => 60
        ];
    }

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
        $this->assertInvalidData(['categories_id' => 'test', 'genres_id' => 'test'], 'array');
        $this->assertInvalidData(['categories_id' => [12], 'genres_id' => [12]], 'exists');
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

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);
        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);
        $request = \Mockery::mock(Request::class);
        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());
        $has_error = false;
        try {
            $controller->store($request);
        } catch (\Exception $exception) {
            $this->assertCount(1, Video::all());
            $has_error = true;
        }
        $this->assertTrue($has_error);
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
        $this->assertHasCategory($response->json('id'), $category->id);
        $this->assertHasGenre($response->json('id'), $genre->id);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->video);
        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);
        $controller
            ->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);
        $request = \Mockery::mock(Request::class);
        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());
        $has_error = false;
        try {
            $controller->update($request, $this->video->id);
        } catch (\Exception $exception) {
            $this->assertCount(1, Video::all());
            $has_error = true;
        }
        $this->assertTrue($has_error);
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

    public function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    public function assertHasGenre($videoId, $genreId)
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
