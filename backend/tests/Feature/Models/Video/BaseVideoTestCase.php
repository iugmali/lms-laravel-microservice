<?php


namespace Tests\Feature\Models\Video;


use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

abstract class BaseVideoTestCase extends TestCase
{
    use DatabaseMigrations;
    protected $data;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->data = [
            'title' => 'covid19',
            'description' => 'descricao do video que pode ser longa ou curta como esta',
            'year_launched' => 2020,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90
        ];
    }

}
