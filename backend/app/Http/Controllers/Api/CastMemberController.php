<?php


namespace App\Http\Controllers\Api;


use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;

class CastMemberController extends BaseController
{
    private $validation_rules;

    public function __construct()
    {
        $this->validation_rules = [
            'name' => 'required|max:255',
            'type' => 'required|in:' . implode(',',[CastMember::TYPE_DIRECTOR,CastMember::TYPE_ACTOR])
        ];
    }
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
    protected function resourceCollection()
    {
        return $this->resource();
    }
    protected function resource()
    {
        return CastMemberResource::class;
    }
}
