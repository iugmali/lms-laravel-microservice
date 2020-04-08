<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    private $validation_rules = [
        'name' => 'required|max:255',
        'description' => 'nullable',
        'is_active' => 'boolean',
        'genres_id' => 'required|array|exists:genres,id,deleted_at,NULL'
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        /** @var Category $obj */
        $obj = \DB::transaction(function () use ($request, $validatedData, $self) {
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrfail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $obj = \DB::transaction(function () use ($request, $validatedData, $obj, $self) {
            $obj->update($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function handleRelations($category, Request $request){
        $category->genres()->sync($request->get('genres_id'));
    }

    protected function model()
    {
        return Category::class;
    }
    protected function rulesStore()
    {
        return $this->validation_rules;
    }
    protected function rulesUpdate()
    {
        return $this->validation_rules;
    }
    protected function resourceCollection()
    {
        return $this->resource();
    }
    protected function resource()
    {
        return CategoryResource::class;
    }
}
