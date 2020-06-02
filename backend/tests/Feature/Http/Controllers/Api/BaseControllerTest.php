<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Resources\CategoryResourceStub;
use Tests\TestCase;


class BaseControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex() {
        $category = CategoryStub::create(['name' => 'Shows', 'description' => 'Shows legais']);

        $resource = $this->controller->index();
        $serialized = $resource->response()->getData(true);

        $this->assertEquals([$category->toArray()], $serialized['data']);
        $this->assertArrayHasKey('meta', $serialized);
        $this->assertArrayHasKey('links', $serialized);
    }

    public function testValidationInStore()
    {
        $this->expectException(ValidationException::class);
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn(['name' => '']);
        $obj = $this->controller->store($request);
    }

    public function testStore()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn(['name' => 'testname', 'description' => 'testdescription']);
        $resource = $this->controller->store($request);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(
            CategoryStub::first()->toArray(),
            $serialized['data']
        );
    }

    public function testFindOrFailFetchModel()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $reflectionClass = new \ReflectionClass(BaseController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);
        $resource = $reflectionMethod->invokeArgs($this->controller, [$category->id]);
        $this->assertInstanceOf(CategoryStub::class, $resource);
    }

    public function testFindOrFailException()
    {
        $this->expectException(ModelNotFoundException::class);
        $reflectionClass = new \ReflectionClass(BaseController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);
        $resource = $reflectionMethod->invokeArgs($this->controller, [0]);
    }

    public function testShow()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $resource = $this->controller->show($category->id)->response()->getData(true);
        $this->assertEquals($resource['data'], CategoryStub::find($category->id)->toArray());
    }

    public function testUpdate()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn(['name' => 'testnamechanged', 'description' => 'testdescriptionchanged']);
        $resource = $this->controller->update($request, $category->id)->response()->getData(true);
        $category->refresh();
        $this->assertEquals($resource['data'], $category->toArray());
    }

    public function testDestroy()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $response = $this->controller->destroy($category->id);
        $this->createTestResponse($response)->assertStatus(204);
        $this->assertCount(0, CategoryStub::all());
    }

}
