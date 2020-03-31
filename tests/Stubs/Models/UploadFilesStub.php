<?php


namespace Tests\Stubs\Models;
use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;

class UploadFilesStub extends Model
{
  use UploadFiles;

  public static $filefields = ['file1', 'file2', 'file3'];


    protected function uploadDir()
  {
      return "1";
  }
}
