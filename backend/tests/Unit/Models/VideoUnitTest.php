<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Video;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Traits\Uuid;
use Tests\TestCase;

class VideoUnitTest extends TestCase
{

    use DatabaseMigrations;
    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIfUseTraits()
    {
        $traits = [SoftDeletes::class , Uuid::class, UploadFiles::class];
        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    public function testFillableAttribute()
    {
        $fillable = ['title','description','year_launched','opened','rating','duration','video_file','thumb_file','trailer_file','banner_file'];
        $this->assertEqualsCanonicalizing($fillable, $this->video->getFillable());
    }

    public function testDatesAttribute()
    {
        $dates = ['created_at', 'updated_at', 'deleted_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->video->getDates());
        }
        $this->assertCount(count($dates), $this->video->getDates());
    }

    public function testCasts()
    {
        $casts = [
            'id' => 'string',
            'opened' => 'boolean',
            'year_launched' => 'integer',
            'duration' => 'integer'
        ];
        $this->assertEquals($casts, $this->video->getCasts());
    }

    public function testFileFields()
    {
        $fileFields = ['video_file', 'thumb_file', 'trailer_file', 'banner_file'];
        $this->assertEqualsCanonicalizing($fileFields, $this->video::$fileFields);
    }

    public function testIncrement()
    {
        $this->assertFalse($this->video->incrementing);
    }

}
