<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class VideosTableSeeder extends Seeder
{

    private $allGenres;
    private $relations = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);
        $self = $this;
        $this->allGenres = Genre::all();
        Model::reguard(); // ativa mass assigment
        factory(Video::class, 5)
            ->make()
            ->each(function (Video $video) use($self) {
                $self->fetchRelations();
                Video::create(
                    array_merge($video->toArray(),
                        [
                            'thumb_file' => $self->getImageFile(),
                            'banner_file' => $self->getImageFile(),
                            'video_file' => $self->getVideoFile(),
                            'trailer_file' => $self->getVideoFile(),
                        ],
                        $this->relations)
                );
            });
        Model::unguard();
    }

    public function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $genresId = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $genresId;
    }

    public function getImageFile()
    {
        return new UploadedFile(
            storage_path('faker/images/image.JPG'),
            'image.JPG'
        );
    }
    public function getVideoFile()
    {
        return new UploadedFile(
            storage_path('faker/videos/video.MP4'),
            'video.MP4'
        );
    }
}
