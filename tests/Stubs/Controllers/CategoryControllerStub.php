<?php


namespace Tests\Stubs\Controllers;


use App\Http\Controllers\Api\BaseController;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BaseController
{

    private $validation_rules = [
        'name' => 'required|max:255',
        'description' => 'nullable',
        'is_active' => 'boolean'
    ];
    protected function model()
    {
        return CategoryStub::class;
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
