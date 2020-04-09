<?php


namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestUploads, TestResources;

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }
    public function testInvalidationTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }
    public function testInvalidationBannerField()
    {
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            Video::BANNER_FILE_MAX_SIZE,
            'image'
        );
    }
    public function testInvalidationThumbField()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();
        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + $files);
        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);
        $video = Video::find($this->getIdFromResponse($response));
        $this->assertIfFilesUrlExists($video, $response);
        $resource = new VideoResource($video);
        $this->assertResource($response, $resource);
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();
        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $files);
        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);
        $video = Video::find($this->getIdFromResponse($response));
        $this->assertIfFilesUrlExists($video, $response);
        $resource = new VideoResource($video);
        $this->assertResource($response, $resource);

        $newFiles = [
            'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg'),
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'trailer_file' => UploadedFile::fake()->create('trailer_file.mp4'),
            'banner_file' => UploadedFile::fake()->image('banner_file.jpg'),
        ];
        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $newFiles);
        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, Arr::except($files, ['thumb_file', 'video_file','trailer_file','banner_file']) + $newFiles);
        $resource = new VideoResource(Video::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
        $id = $this->getIdFromResponse($response);
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $this->getIdFromResponse($response);
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles()
    {
        return [
            'thumb_file' => UploadedFile::fake()->image('thumb.jpeg'),
            'video_file' => UploadedFile::fake()->create('video.mp4'),
            'trailer_file' => UploadedFile::fake()->create('trailer.mp4'),
            'banner_file' => UploadedFile::fake()->image('banner.jpg')
        ];
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
