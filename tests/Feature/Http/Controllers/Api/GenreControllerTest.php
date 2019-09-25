<?php


namespace Tests\Feature\Http\Controllers\Api;


use App\Models\Genre;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
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
}
