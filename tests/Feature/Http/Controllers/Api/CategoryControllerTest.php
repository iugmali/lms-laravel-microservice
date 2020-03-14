<?php


namespace Tests\Feature\Http\Controllers\Api;


use App\Http\Controllers\Api\BaseController;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;


class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
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

    private function assertInvadidRequired(TestResponse $response){
        $this->assertInvalidFields($response, 'name', 'required', []);
        $response->assertJsonMissingValidationErrors(['is_active']);
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
        $this->category = factory(Category::class)->create([
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
        $response = $this->json('DELETE', route('categories.destroy', ['category'  => $this->category->id]));
        $response->assertStatus(204);
        $this->assertTrue(Category::find($this->category->id));
        $this->assertNull(Category::withTrashed()->find($this->category->id));
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
