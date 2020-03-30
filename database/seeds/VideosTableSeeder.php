<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Seeder;

class VideosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genres = Genre::all();
        factory(Video::class, 100)
            ->create()
            ->each(function (Video $video) use($genres) {
                $subgenres = $genres->random(5)->load('categories');
                $categoriesId = [];
                foreach ($subgenres as $genre) {
                    array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
                }
                $categoriesId = array_unique($categoriesId);
                $video->categories()->attach($categoriesId);
                $video->genres()->attach($subgenres->pluck('id')->toArray());
            });;
    }
}
