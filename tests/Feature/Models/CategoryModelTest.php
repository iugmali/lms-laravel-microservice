<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryModelTest extends TestCase
{

    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKey = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoryKey);
    }

    public function testCreate()
    {
        $category = Category::create(['name' => 'teste']);
        $category->refresh();

        $this->assertEquals(36, strlen($category->id));
        $this->assertEquals('teste', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create(['name' => 'teste', 'description' => 'descricao']);
        $category->refresh();
        $this->assertEquals('descricao', $category->description);

        $category = Category::create(['name' => 'teste', 'is_active' => false]);
        $category->refresh();
        $this->assertFalse($category->is_active);
    }

    public function testUpdate()
    {
        $category = Category::create(['name' => 'teste', 'description' => 'description', 'is_active' => false]);
        $category->refresh();

        $data = ['name' => 'teste2', 'description' => 'description_updated', 'is_active' => true];
        $category->update($data);

        foreach ($data as $key => $value){
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDelete()
    {
        $category = Category::create(['name' => 'teste', 'is_active' => false]);
        $category->delete();
        $this->assertNull(Category::find($category->id));
        $category->restore();
        $this->assertNotNull(Category::find($category->id));
    }
}
