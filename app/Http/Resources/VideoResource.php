<?php

namespace App\Http\Resources;

use App\Models\Video;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{

    public $thumb_file_url, $video_file_url, $trailer_file_url, $banner_file_url;
    public function __construct(Video $video)
    {
        parent::__construct($video);
        // loading additional attributes
        $video->categories;
        $video->genres;
        // load files url
        $this->thumb_file_url = $video->getThumbFileUrlAttribute();
        $this->video_file_url = $video->getVideoFileUrlAttribute();
        $this->trailer_file_url = $video->getTrailerFileUrlAttribute();
        $this->banner_file_url = $video->getBannerFileUrlAttribute();
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request) + [
                'categories' => CategoryResource::collection($this->categories),
                'genres' => GenreResource::collection($this->genres),
                'links' => [
                    'thumb_file_url' => $this->thumb_file_url,
                    'video_file_url' => $this->video_file_url,
                    'trailer_file_url' => $this->trailer_file_url,
                    'banner_file_url' => $this->banner_file_url
                ]
            ];
    }
}
