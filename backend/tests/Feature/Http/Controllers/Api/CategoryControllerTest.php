<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $category;
    private $sendData;
    private $serializedFields = [
        'id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
        $this->sendData = [
            'name' => 'teste',
            'description' => 'blabla'
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
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

        $resource = CategoryResource::collection(collect([$this->category]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->serializedFields]);
        $resource = new CategoryResource(Category::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
    }

    public function testInvalidData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidStoreAction($data, 'required');
        $this->assertInvalidUpdateAction($data, 'required');
        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidUpdateAction($data, 'max.string', ['max' => 255]);
        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidStoreAction($data, 'boolean');
        $this->assertInvalidUpdateAction($data, 'boolean');
        $data = [
            'genres_id' => 'a'
        ];
        $this->assertInvalidStoreAction($data, 'array');
        $this->assertInvalidUpdateAction($data, 'array');
        $data = [
            'genres_id' => [100]
        ];
        $this->assertInvalidStoreAction($data, 'exists');
        $this->assertInvalidUpdateAction($data, 'exists');
        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            'genres_id' => [$genre->id]
        ];
        $this->assertInvalidStoreAction($data, 'exists');
        $this->assertInvalidUpdateAction($data, 'exists');
    }

    public function testStore()
    {
        $genreId = factory(Genre::class)->create()->id;
        $data = [
            'name' => 'teste'
        ];
        $response = $this->assertStore($data + ['genres_id' => [$genreId]], $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['data' =>  $this->serializedFields]);
        $resource = new CategoryResource(Category::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
        $this->assertHasGenre($genreId,$this->getIdFromResponse($response));
        $data = [
            'name' => 'teste',
            'description' => 'testando',
            'is_active' => false
        ];
        $this->assertStore($data + ['genres_id' => [$genreId]], $data + ['description' => 'testando', 'is_active' => false]);
        $this->assertHasGenre($genreId,$this->getIdFromResponse($response));
    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(CategoryController::class)
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
            $this->assertCount(1, Category::all());
            $has_error = true;
        }
        $this->assertTrue($has_error);
    }


    public function testUpdate()
    {
        $genreId = factory(Genre::class)->create()->id;
        $this->category = factory(Category::class)->create([
            'name' => 'teste',
            'description' => 'description',
            'is_active' => false
        ]);
        $data = [
            'name' => 'teste',
            'description' => 'teste',
            'is_active' => true
        ];
        $response = $this->assertUpdate($data + ['genres_id' => [$genreId]], $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertHasGenre($genreId,$this->getIdFromResponse($response));
        $resource = new CategoryResource(Category::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
        $data = [
            'name' => 'teste',
            'description' => '',
            'is_active' => false
        ];
        $this->assertUpdate($data + ['genres_id' => [$genreId]], array_merge($data, ['description' => null]));
        $data['description'] = 'teste';
        $this->assertUpdate($data + ['genres_id' => [$genreId]], $data);
        $this->assertHasGenre($genreId,$this->getIdFromResponse($response));
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(CategoryController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->category);
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
            $controller->update($request, $this->category->id);
        } catch (\Exception $exception) {
            $this->assertCount(1, Category::all());
            $has_error = true;
        }
        $this->assertTrue($has_error);
    }

    public function testSyncGenres()
    {
        $genresId = factory(Genre::class, 3)->create()->pluck('id')->toArray();
        $sendData = [
            'name' => 'teste',
            'genres_id' => [$genresId[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertHasGenre($genresId[0], $this->getIdFromResponse($response));
        $sendData = [
            'name' => 'teste',
            'genres_id' => [$genresId[1],$genresId[2]]
        ];
        $response = $this->json('PUT', route('categories.update', ['category' => $this->getIdFromResponse($response)]), $sendData);
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $this->getIdFromResponse($response),
            'genre_id' => $genresId[0]
        ]);
        $this->assertHasGenre($genresId[1],$this->getIdFromResponse($response));
        $this->assertHasGenre($genresId[2],$this->getIdFromResponse($response));
    }

    public function assertHasGenre($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $genreId,
            'category_id' => $categoryId
        ]);
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('categories.destroy', ['category'  => $this->category->id]));
        $response->assertStatus(204);
        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }
    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }
    protected function model() {
        return Category::class;
    }
}
