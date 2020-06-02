<?php


namespace Tests\Stubs\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class GenreResourceStub extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
