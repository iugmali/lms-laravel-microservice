<?php


namespace App\Http\Controllers\Api;


use App\Models\CastMember;

class CastMemberController extends BaseController
{
    private $validation_rules = [
        'name' => 'required|max:255',
        'description' => 'nullable',
        'is_active' => 'boolean'
    ];
    protected function model()
    {
        return CastMember::class;
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