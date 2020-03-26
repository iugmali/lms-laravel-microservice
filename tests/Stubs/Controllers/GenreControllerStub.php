<?php


namespace Tests\Stubs\Controllers;


use App\Http\Controllers\Api\BaseController;
use Tests\Stubs\Models\GenreStub;

class GenreControllerStub extends BaseController
{

    private $validation_rules = [
        'name' => 'required|max:255'
    ];
    protected function model()
    {
        return GenreStub::class;
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
