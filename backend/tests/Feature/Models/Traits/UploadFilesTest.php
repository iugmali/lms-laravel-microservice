<?php


namespace Tests\Feature\Models\Traits;


use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private $obj;
    protected function setUp() : void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
    }

    public function testMakeOldFilesOnSaving()
    {
        $this->obj->fill([
            'name' => 'test',
            'file1' => 'test.mp4',
            'file2' => 'test2.mp4',
        ]);
        $this->obj->save();
        $this->assertCount(0, $this->obj->oldFiles);
        $this->obj->update([
            'name' => 'test2',
            'file2' => 'test3.mp4'
        ]);
        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->obj->oldFiles);
    }

    public function testMakeOldFilesNullOnSaving()
    {
        $this->obj->fill([
            'name' => 'test'
        ]);
        $this->obj->save();
        $this->obj->update([
            'file1' => 'test.mp4',
            'file2' => 'test2.mp4'
        ]);
        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }

}
