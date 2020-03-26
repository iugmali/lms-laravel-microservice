<?php


namespace App\Http\Controllers\Api;


use App\Models\Video;

class VideoController extends BaseController
{
    private $validation_rules;

    public function __construct()
    {
        $this->validation_rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'opened' => 'boolean',
            'year_launched' => 'required'
        ];
    }
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
