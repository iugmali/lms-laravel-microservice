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

    public function testExtractFiles()
    {
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertEquals(['file1' => 'test'], $attributes);
        $this->assertCount(1, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test', 'file2' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertEquals(['file1' => 'test', 'file2' => 'test'], $attributes);
        $this->assertCount(2, $attributes);
        $this->assertCount(0, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = ['file1' => $file1, 'file2' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'file2' => 'test'], $attributes);
        $this->assertEquals([$file1], $files);
        $this->assertCount(2, $attributes);
        $this->assertCount(1, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mkv');
        $attributes = ['file1' => $file1, 'file2' => $file2, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'file2' => $file2->hashName(), 'other' => 'test'], $attributes);
        $this->assertEquals([$file1, $file2], $files);
        $this->assertCount(3, $attributes);
        $this->assertCount(2, $files);
    }

}
