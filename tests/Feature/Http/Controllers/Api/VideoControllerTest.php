<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestValidation;
use Tests\Traits\TestSaves;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;

//../vendor/bin/phpunit Feature/

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidation;
    use TestSaves;
    private $video;
    private $sendData;
    protected function setUp(): void
    {
        parent::setUp();
        $this->video=factory(Video::class)->create(['opened'=>false]);
        $this->sendData=['title'=>'title',
                        'description'=>'description',
                        'year_launched'=>2010,
                        'rating'=>Video::RATING_LIST[0],
                        'duration'=>90];
    }
    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response->assertStatus(200)
                ->assertJson([$this->video->toArray()]);
    }

    public function testInvalidationRequired(){
        $data=['title'=>'',
                'description'=>'',
                'year_launched'=>'',
                'rating'=>'',
                'duration'=>'',
                'categories_id'=>'',
                'genres_id'=>''
              ];
        $this->assertInvalidationInStoreAction($data,'required');
        $this->assertInvalidationInUpdateAction($data,'required');
      
    }

    public function testInvalidationMax(){
        $data=['title'=>str_repeat('a',256)];
        $this->assertInvalidationInStoreAction($data,"max.string",['max'=>255]);
        $this->assertInvalidationInUpdateAction($data,"max.string",['max'=>255]);
       
    }

    public function testInvalidationInteger(){
        $data=['duration'=>'s'];
        $this->assertInvalidationInStoreAction($data,"integer");
        $this->assertInvalidationInUpdateAction($data,"integer");
    }

    public function testInvalidationYearLaunched(){
        $data=['year_launched'=>'s'];
        $this->assertInvalidationInStoreAction($data,"date_format",["format"=>"Y"]);
        $this->assertInvalidationInUpdateAction($data,"date_format",["format"=>"Y"]);
    }

    public function testInvalidationOpenedField(){
        $data=['opened'=>'s'];
        $this->assertInvalidationInStoreAction($data,"boolean");
        $this->assertInvalidationInUpdateAction($data,"boolean");
    }

    public function testInvalidationRatingField(){
        $data=['rating'=>0];
        $this->assertInvalidationInStoreAction($data,"in");
        $this->assertInvalidationInUpdateAction($data,"in");
    }

    public function testInvalidationCategoriesIdField(){
        $data=[
            'categories_id'=>'a'
        ];
        $this->assertInvalidationInStoreAction($data,'array');
        $this->assertInvalidationInUpdateAction($data,'array');

        $data=[
            'categories_id'=>[100]
        ];
        $this->assertInvalidationInStoreAction($data,'exists');
        $this->assertInvalidationInUpdateAction($data,'exists');
    }

    public function testInvalidationGenreIdField(){
        $data=[
            'genres_id'=>'a'
        ];
        $this->assertInvalidationInStoreAction($data,'array');
        $this->assertInvalidationInUpdateAction($data,'array');

        $data=[
            'genres_id'=>[100]
        ];
        $this->assertInvalidationInStoreAction($data,'exists');
        $this->assertInvalidationInUpdateAction($data,'exists');
    }
    public function testShow()
    {
        $response = $this->get(route('videos.show',$this->video->id));

        $response->assertStatus(200)
                ->assertJson($this->video->toArray());
    }

   public function testSave(){
       $category=factory(Category::class)->create();
       $genre=factory(Genre::class)->create();
       $data=[
            ['send_data'=>$this->sendData+[
                'categories_id'=>[$category->id],
                'genres_id'=>[$genre->id]],
             'test_data'=>$this->sendData+['opened'=>false]
            ],
            ['send_data'=>$this->sendData+[
                'opened'=>true,
                'categories_id'=>[$category->id],
                'genres_id'=>[$genre->id]
                ],
            'test_data'=>$this->sendData+['opened'=>true]
            ],
            ['send_data'=>$this->sendData+[
                    'rating'=>Video::RATING_LIST[1],
                    'categories_id'=>[$category->id],
                    'genres_id'=>[$genre->id]
                ],
            'test_data'=>$this->sendData+['rating'=>Video::RATING_LIST[1]]
            ]
       ];
       foreach($data as $key=>$value){
            $response=$this->assertStore(
                $value['send_data'],
                $value['test_data']+['deleted_at'=>null]);
                $response->assertJsonStructure(
                    ['created_at','updated_at']);
                        
            $response=$this->assertUpdate(
                $value['send_data'],
                $value['test_data']+['deleted_at'=>null]);
                $response->assertJsonStructure(
                    ['created_at','updated_at']);
                   
       }
   }

   private function MockerController($ruleMethodName){
        $controller=\Mockery::mock(VideoController::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller
            ->shouldReceive($ruleMethodName)
            ->withAnyArgs()
            ->andReturn([]);
 
        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(New TestException());
         
        return $controller;
   }
    public function testRollbackStore(){
        $controller=$this->MockerController('rulesStore');
        $request=\Mockery::mock(Request::class);
        try{
            $controller->store($request);
        }catch(TestException $e){
            $this->assertCount(1,Video::all());
        }
    }
    public function testRollbackUpdate(){
        $controller=$this->MockerController('rulesUpdate');
        $request=\Mockery::mock(Request::class);
        try{
            $beforeVideo=$this->video->find($this->video->id);
            $controller->update($request,$this->video->id);
        }catch(TestException $e){
            $afterVideo=$this->video->find($this->video->id);
            $this->assertEquals($beforeVideo,$afterVideo);
        }
    }

    public function testDestroy(){
        $response=$this->json('DELETE',route('videos.destroy',['video'=>$this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function routeStore(){
        return route('videos.store');
    }
    protected function routeUpdate(){
        return route('videos.update',['video'=>$this->video->id]);
    }
    protected function model(){
        return Video::class;
    }
}
