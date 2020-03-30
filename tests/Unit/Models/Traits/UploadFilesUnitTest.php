<?php


namespace Tests\Unit\Models\Traits;

use Illuminate\Http\UploadedFile;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesUnitTest extends TestCase
{
    private $obj;

    protected function setUp() : void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
    }

    public function testUploadFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.mpeg');
        $file2 = UploadedFile::fake()->create('video2.mpeg');
        $this->obj->uploadFiles([$file1,$file2]);
        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mkv');
        $this->obj->uploadFile($file);
        $filename = $file->hashName();
        $this->obj->deleteFile($filename);
        \Storage::assertMissing("1/{$filename}");
        $file = UploadedFile::fake()->create('video.mkv');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);
        \Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.avi');
        $file2 = UploadedFile::fake()->create('video2.avi');
        $this->obj->uploadFiles([$file1,$file2]);
        $filename1 = $file1->hashName();
        $this->obj->deleteFiles([$filename1,$file2]);
        \Storage::assertMissing("1/{$filename1}");
        \Storage::assertMissing("1/{$file2->hashName()}");
    }

}
