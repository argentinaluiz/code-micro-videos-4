<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Validation\ValidationException;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\TestCase;
use Illuminate\Http\Request;

//../vendor/bin/phpunit Feature/
//../vendor/bin/phpunit Feature/Http/Controllers/Api/BasicCrudControllerTest.php


class BasicCrudControllerTest extends TestCase
{
    private $controller;
    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller=new CategoryControllerStub();
    }

    protected function tearDown():void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }
    public function testIndex(){
        $category=CategoryStub::create(['name'=>'test_name','description'=>'test_description']);
        $result=$this->controller->index()->toArray();
        $this->assertEquals([$category->toArray()],$result);

    }
    public function testInvalidationDataInStore(){
            $this->expectException(ValidationException::class);
            $request = \Mockery::mock(Request::class);
            $request->shouldReceive('all')
                ->once()
                ->andReturn(['name'=>'']);
            $this->controller->store($request);
    }
}
