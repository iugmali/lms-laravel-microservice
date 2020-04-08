<?php


namespace Tests\Feature\Http\Controllers\Api\VideoController;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $video;
    protected $sendData;
    protected $serializedFields = [
        'title',
        'description',
        'year_launched',
        'opened',
        'duration',
        'rating',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create(['opened' => false]);
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $this->sendData = [
            'title' => 'teste',
            'description' => str_repeat('a', 400),
            'year_launched' => 2000,
            'rating' => Video::RATING_LIST[0],
            'duration' => 60,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ];
    }

}
