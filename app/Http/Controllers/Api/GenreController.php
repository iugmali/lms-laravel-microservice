<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;

class GenreController extends BaseController
{
    private $validation_rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];
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
