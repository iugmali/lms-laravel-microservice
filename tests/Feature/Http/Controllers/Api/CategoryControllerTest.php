<?php


namespace Tests\Feature\Http\Controllers\Api;


use App\Models\Category;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));
        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testInvalidData()
    {
        $response = $this->json("POST",route('categories.store'), []);
        $this->assertInvadidRequired($response);
        $response = $this->json("POST",route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvadidMax($response);
        $this->assertInvadidBoolean($response);
        $category = factory(Category::class)->create();
        $response = $this->json("PUT",route('categories.update'), ['category' => $category->id], []);
        $this->assertInvadidRequired($response);
        $response = $this->json("PUT",route('categories.update'), ['category' => $category->id], [
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
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'teste'
        ]);
        $id = $response->json('id');
        $category = Category::find($id);
        $category->refresh();
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'teste',
            'description' => 'testando',
            'is_active' => 'false'
        ]);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'name' => 'teste',
            'description' => 'testando',
            'is_active' => false
        ]);
        $response = $this->json('PUT', route('categories.update'), ['category => $category->id'],[
            'name' => 'teste',
            'description' => 'testando',
            'is_active' => 'false'
        ]);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

    }
}
