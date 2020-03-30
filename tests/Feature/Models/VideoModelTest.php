<?php


namespace Tests\Feature\Models;


use App\Http\Controllers\Api\VideoController;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;

class VideoModelTest extends TestCase
{
    use DatabaseMigrations;

    public function testRollbackCreate()
    {
        $has_error = false;
        try {
            Video::create([
                'title' => 'teste2',
                'description' => str_repeat('a', 300),
                'year_launched' => 2014,
                'opened' => true,
                'rating' => '18',
                'duration' => 100,
                'categories_id' => [0,1,2]
            ]);
        } catch (QueryException $exception) {
            $this->assertCount(0, Video::all());
            $has_error = true;
        }
        $this->assertTrue($has_error);
    }

    public function testRollbackUpdate()
    {
        $has_error = false;
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;
        try {
            $video->update([
                'title' => 'teste2',
                'description' => str_repeat('a', 300),
                'year_launched' => 2014,
                'opened' => true,
                'rating' => '18',
                'duration' => 100,
                'categories_id' => [0,1,2]
            ]);
        } catch (QueryException $exception) {
            $this->assertDatabaseHas('videos', [
                'title' => $oldTitle
            ]);
            $has_error = true;
        }
        $this->assertTrue($has_error);
    }


}
