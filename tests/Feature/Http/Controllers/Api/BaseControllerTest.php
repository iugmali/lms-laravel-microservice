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

//    public function testIndex() {
//        // nao deu por enquanto
//        $category = CategoryStub::create(['name' => 'Shows', 'description' => 'Shows legais']);
//
//        $result = $this->controller->index();
//        $resource = CategoryResourceStub::collection(collect([$category]));
//
//        dump($result);
//
////        $this->assertEqualsCanonicalizing($resource, $result);
////        $this->assertEquals([$category->toArray()], $result['data']);
//    }

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
        $obj = $this->controller->store($request);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $obj->all()->toArray()[0]
        );
    }

    public function testFindOrFailFetchModel()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $reflectionClass = new \ReflectionClass(BaseController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invokeArgs($this->controller, [$category->id]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testFindOrFailException()
    {
        $this->expectException(ModelNotFoundException::class);
        $reflectionClass = new \ReflectionClass(BaseController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invokeArgs($this->controller, [0]);
    }

    public function testShow()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $result = $this->controller->show($category->id);
        $this->assertEquals($result->all()->toArray()[0], CategoryStub::find($category->id)->toArray());
    }

    public function testUpdate()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn(['name' => 'testnamechanged', 'description' => 'testdescriptionchanged']);
        $result = $this->controller->update($request, $category->id);
        $this->assertEquals($result->all()->toArray()[0], CategoryStub::find(1)->toArray());
    }

    public function testDestroy()
    {
        $category = CategoryStub::create(['name' => 'testname', 'description' => 'testdescription']);
        $response = $this->controller->destroy($category->id);
        $this->createTestResponse($response)->assertStatus(204);
        $this->assertCount(0, CategoryStub::all());
    }

}
