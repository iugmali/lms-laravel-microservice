<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;
    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genre.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('categories.show', ['genre' => $genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testInvalidData()
    {
        $response = $this->json("POST",route('genres.store'), []);
        $this->assertInvadidRequired($response);
        $response = $this->json("POST",route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvadidMax($response);
        $this->assertInvadidBoolean($response);
        $genre = factory(Genre::class)->create();
        $response = $this->json("PUT",route('genres.update'), ['genre' => $genre->id], []);
        $this->assertInvadidRequired($response);
        $response = $this->json("PUT",route('genres.update'), ['genre' => $genre->id], [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvadidMax($response);
        $this->assertInvadidBoolean($response);
    }

    private function assertInvadidRequired(TestResponse $response){
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }
    private function assertInvadidMax(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255]),
            ]);
    }
    private function assertInvadidBoolean(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testStore()
    {
        $data = [
            'name' => 'teste'
        ];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $data = [
            'name' => 'teste',
            'description' => 'testando',
            'is_active' => 'false'
        ];
        $this->assertStore($data, $data + ['description' => 'testando', 'is_active' => false]);
    }

    public function testUpdate()
    {
        $this->category = factory(Genre::class)->create([
            'name' => 'teste',
            'description' => 'testando',
            'is_active' => false
        ]);
        $data = [
            'name' => 'teste',
            'description' => '',
            'is_active' => false
        ];
        $response = $this->assertUpdate($data, $data);
    }

    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();
        $genre->refresh();
        $response = $this->json('DELETE', route('genres.destroy', ['genre'  => $genre->id]));
        $response->assertStatus(204);
        $this->assertTrue(Genre::find($genre->id));
        $this->assertNull(Genre::withTrashed()->find($genre->id));
    }
}
