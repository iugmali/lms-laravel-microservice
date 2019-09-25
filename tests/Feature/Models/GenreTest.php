<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{

    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);
        $genreKey = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $genreKey);
    }

    public function testCreate()
    {
        $genre = Genre::create(['name' => 'teste']);
        $genre->refresh();
        $this->assertEquals(36, strlen($genre->id));
        $this->assertEquals('teste', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create(['name' => 'teste', 'is_active' => false]);
        $genre->refresh();
        $this->assertFalse($genre->is_active);
    }

    public function testUpdate()
    {
        $genre = Genre::create(['name' => 'teste', 'is_active' => false]);
        $data = ['name' => 'teste2', 'is_active' => true];
        $genre->update($data);

        foreach ($data as $key => $value){
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        $genre = Genre::create(['name' => 'teste', 'is_active' => false]);
        $genre->delete();
        $this->assertNull(Genre::find($genre->id));
        $genre->restore();
        $this->assertNotNull(Genre::find($genre->id));
    }

}
