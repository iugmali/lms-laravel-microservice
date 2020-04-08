<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseController extends Controller
{

    protected $paginationSize = 15;
    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();
    protected abstract function resource();
    protected abstract function resourceCollection();

    public function index()
    {
        $collection = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);
        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($this->resourceCollection());
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($collection)
            : $resourceCollectionClass::collection($collection);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function show($id)
    {
        $obj = $this->findOrfail($id);
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrfail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validatedData);
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function destroy($id)
    {
        $obj = $this->findOrfail($id);
        $obj->delete();
        return response()->noContent(); // 204
    }

    protected function findOrfail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

}
