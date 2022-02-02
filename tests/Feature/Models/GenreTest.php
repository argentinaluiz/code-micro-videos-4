<?php

namespace tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
//../vendor/bin/phpunit Feature/
class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList(){
        factory(Genre::class,1)->create();
        $genre=Genre::all();
        $this->assertCount(1,$genre);
        $genreKey=array_keys($genre->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            ['id','name','is_active','created_at','updated_at','deleted_at'],
            $genreKey   
        );
    }
    public function testCreate()
    {;
        $genre=Genre::create([
            'name'=>'test1'
        ]);
        $genre->refresh();
        $this->assertEquals('test1',$genre->name);
        $this->assertTrue($genre->is_active);

        $genre=Genre::create([
            'name'=>'test2',
            'is_active'=>true
        ]);
        $this->assertTrue($genre->is_active);
        $genre=Genre::create([
            'name'=>'test3',
            'is_active'=>false
        ]);
        $this->assertFalse($genre->is_active);
        $genre=factory(Genre::class)->create();
        $pattern = "/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}/i";
        $this->assertTrue(preg_match($pattern, $genre->id)===1);

    }

    public function testDelete(){
        $genre=factory(Genre::class)->create();
        $id=$genre->id;
        $this->assertTrue($genre->delete($id));
        $this->assertNull(Genre::find($id));
    }

    public function testUpdate(){
        $genre=factory(Genre::class)->create();
        $date=['name'=>'test_name_update',
                'is_active'=>true];
        $genre->update($date);
        foreach($date as $key=>$value){
            $this->assertEquals($value,$genre->{$key});
        }
    }
}
