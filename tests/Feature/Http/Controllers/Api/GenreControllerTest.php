<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
        $this->sendData = [
            'name' => 'teste'
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testInvalidData()
    {
        $data = [
            'name' => '',
            'categories_id' => ''
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
            'categories_id' => 'a'
        ];
        $this->assertInvalidStoreAction($data, 'array');
        $this->assertInvalidUpdateAction($data, 'array');
        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidStoreAction($data, 'exists');
        $this->assertInvalidUpdateAction($data, 'exists');
        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidStoreAction($data, 'exists');
        $this->assertInvalidUpdateAction($data, 'exists');
    }

    public function testStore()
    {
        $categoryId = factory(Category::class)->create()->id;
        $data = [
            'name' => 'teste'
        ];
        $response = $this->assertStore($data + ['categories_id' => [$categoryId]], $data + ['is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertHasCategory($response->json('id'), $categoryId);
        $data = [
            [
                'name' => 'teste'
            ],
            [
                'name' => 'teste',
                'is_active' => false
            ]
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStore($value + ['categories_id' => [$categoryId]], $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $categoryId);
        }
    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
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
            $this->assertCount(1, Genre::all());
            $has_error = true;
        }
        $this->assertTrue($has_error);
    }


    public function testUpdate()
    {
        $categoryId = factory(Category::class)->create()->id;
        $this->genre = factory(Genre::class)->create([
            'name' => 'teste',
        ]);
        $data = [
            'name' => 'teste',
            'is_active' => false
        ];
        $response = $this->assertUpdate($data + ['categories_id' => [$categoryId]], $data + ['deleted_at' => null]);
        $this->assertHasCategory($response->json('id'), $categoryId);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);
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
            $controller->update($request, $this->genre->id);
        } catch (\Exception $exception) {
            $this->assertCount(1, Genre::all());
            $has_error = true;
        }
        $this->assertTrue($has_error);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $sendData = [
            'name' => 'teste',
            'categories_id' => [$categoriesId[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertHasCategory($response->json('id'), $categoriesId[0]);
        $sendData = [
            'name' => 'teste',
            'categories_id' => [$categoriesId[1],$categoriesId[2]]
        ];
        $response = $this->json('PUT', route('genres.update', ['genre' => $response->json('id')]), $sendData);
        $this->assertDatabaseMissing('category_genre', [
           'category_id' => $categoriesId[0],
           'genre_id' => $response->json('id')
        ]);
        $this->assertHasCategory($response->json('id'), $categoriesId[1]);
        $this->assertHasCategory($response->json('id'), $categoriesId[2]);
    }

    public function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $genreId,
            'category_id' => $categoryId
        ]);
    }

    public function testDestroy()
    {
        $this->genre->refresh();
        $response = $this->json('DELETE', route('genres.destroy', ['genre'  => $this->genre->id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }
    protected function routeStore()
    {
        return route('genres.store');
    }
    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }
    protected function model() {
        return Genre::class;
    }

}
