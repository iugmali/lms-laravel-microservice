<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
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
            ->assertJson($genre->toArray());
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
    }

    public function testStore()
    {
        $data = [
            'name' => 'teste'
        ];
        $response = $this->assertStore($data, $data + ['is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
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
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
        }
    }

    public function testUpdate()
    {
        $this->genre = factory(Genre::class)->create([
            'name' => 'teste',
        ]);
        $data = [
            'name' => 'teste',
            'is_active' => false
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
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
