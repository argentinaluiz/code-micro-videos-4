<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();
    public function index()
    {
        return $this->model()::all();
    }

    protected function findOrFail($id){
        $model=$this->model();
        $keyName=(new $model)->getRouteKeyName();
        return $this->model()::where($keyName,$id)->firstOrFail();
    }

    public function store(Request $request)
    { 
        $validateData=$this->validate($request,$this->rulesStore());
        $obj=$this->model()::create($validateData);
        $obj->refresh();
        return $obj;
    }

    public function show($id)
    {
        $obj=$this->findOrFail($id);
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $validateData=$this->validate($request,$this->rulesUpdate());
        $obj=$this->findOrFail($id);
        $obj->update($validateData);
        return $obj;
    }

    public function destroy($id)
    {
        $obj=$this->findOrFail($id);
        $obj->delete();
        return response()->noContent();
    }
    
}
