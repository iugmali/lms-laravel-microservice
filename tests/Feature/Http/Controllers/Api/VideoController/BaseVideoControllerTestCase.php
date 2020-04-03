<?php


namespace Tests\Feature\Http\Controllers\Api\VideoController;


use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $video;
    protected $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create(['opened' => false]);
        $this->sendData = [
            'title' => 'teste',
            'description' => str_repeat('a', 400),
            'year_launched' => 2000,
            'rating' => '12',
            'duration' => 60
        ];
    }

}
