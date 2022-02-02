<?php

namespace tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
//../vendor/bin/phpunit Feature/
class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testList(){
        factory(Category::class,1)->create();
        $categories=Category::all();
        $this->assertCount(1,$categories);
        $categoryKey=array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id','name','description','is_active','created_at','updated_at','deleted_at'],
            $categoryKey   
        );
    }
    public function testCreate()
    {;
        $category=Category::create([
            'name'=>'test1'
        ]);
        $category->refresh();
        $this->assertEquals('test1',$category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        $category=Category::create([
            'name'=>'test2',
            'description'=>null,
        ]);
        $this->assertNull($category->description);
        $category=Category::create([
            'name'=>'test3',
            'is_active'=>true
        ]);
        $this->assertTrue($category->is_active);
        $category=Category::create([
            'name'=>'test4',
            'is_active'=>false
        ]);
        $this->assertFalse($category->is_active);
        $category=factory(Category::class)->create();
        $pattern = "/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}/i";
        $this->assertTrue(preg_match($pattern, $category->id)===1);

    }

    public function testDelete(){
        $category=factory(Category::class)->create();
        $id=$category->id;
        $this->assertTrue($category->delete($id));
        $this->assertNull(Category::find($id));
        
    }

    public function testUpdate(){
        $category=factory(Category::class)->create(['description'=>'Teste de update','is_active'=>false]);
        $date=['name'=>'test_name_update',
                'description'=>'test_description_update',
                'is_active'=>true];
        $category->update($date);
        foreach($date as $key=>$value){
            $this->assertEquals($value,$category->{$key});
        }
    }
}
