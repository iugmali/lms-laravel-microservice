<?php


namespace App\Http\Controllers\Api;


use App\Models\Video;

class VideoController extends BaseController
{
    private $validation_rules = [
        'name' => 'required|max:255',
        'description' => 'required',
        'is_active' => 'boolean'
    ];
    protected function model()
    {
        return Video::class;
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
