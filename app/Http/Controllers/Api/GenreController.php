<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\Request;

class GenreController extends BaseController
{
    private $validation_rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        /** @var Genre $obj */
        $obj = \DB::transaction(function () use ($request, $validatedData, $self) {
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        return $obj;
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
        return $obj;
    }
    protected function handleRelations($genre, Request $request){
        $genre->categories()->sync($request->get('categories_id'));
    }
    protected function model()
    {
        return Genre::class;
    }
    protected function rulesStore()
    {
        return $this->validation_rules;
    }
    protected function rulesUpdate()
    {
        return $this->validation_rules;
    }
}
