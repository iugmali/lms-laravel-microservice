<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['cast_member' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidStoreAction($data, 'required');
        $this->assertInvalidUpdateAction($data, 'required');
        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidUpdateAction($data, 'max.string', ['max' => 255]);
        $data = [
            'type' => ''
        ];
        $this->assertInvalidStoreAction($data, 'required');
        $this->assertInvalidUpdateAction($data, 'required');
        $data = [
            'type' => 'a'
        ];
        $this->assertInvalidStoreAction($data, 'in');
        $this->assertInvalidUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            [
                'name' => 'teste',
                'description' => str_repeat('a', 400),
                'year_launched' => 2000,
                'opened' => false,
                'rating' => '12',
                'duration' => 60
            ],
            [
                'name' => 'teste2',
                'description' => str_repeat('a', 300),
                'year_launched' => 2014,
                'opened' => true,
                'rating' => '18',
                'duration' => 100
            ]
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
        }
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'teste2',
            'description' => str_repeat('a', 300),
            'year_launched' => 2014,
            'opened' => true,
            'rating' => '18',
            'duration' => 100
        ];
        $this->assertUpdate($data, $data);
    }

    public function testDestroy()
    {
        $this->video->refresh();
        $response = $this->json('DELETE', route('videos.destroy', ['video'  => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }
    protected function routeStore()
    {
        return route('videos.store');
    }
    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }
    protected function model() {
        return Video::class;
    }

}
