<?php


namespace Tests\Traits;


use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\UploadedFile;

trait TestUploads
{
    protected function assertInvalidationFile($field, $extension, $maxSize, $rule, $ruleParams = [])
    {
//        $routes = [
//            [
//                'method' => 'POST',
//                'route' => $this->routeStore()
//            ],
//            [
//                'method' => 'PUT',
//                'route' => $this->routeUpdate()
//            ]
//        ];
//        foreach ($routes as $route) {
//            $file = UploadedFile::fake()->create("$field.1$extension");
//            $category = factory(Category::class)->create();
//            $genre = factory(Genre::class)->create();
//            $genre->categories()->sync($category->id);
//            $response = $this->json($route['method'], $route['route'], [
//                $this->sendData + [
//                    'categories_id' => [$category->id],
//                    'genres_id' => [$genre->id],
//                     $field => $file
//                ]
//            ]);
//            $this->assertInvalidFields($response, [$field], $rule, $ruleParams);
//            $file = UploadedFile::fake()->create("$field.$extension")->size($maxSize + 1);
//            $response = $this->json($route['method'], $route['route'], [
//                $this->sendData + [
//                    'categories_id' => [$category->id],
//                    'genres_id' => [$genre->id],
//                    $field => $file
//                ]
//            ]);
//            $this->assertInvalidFields($response, [$field], 'max.file', ['max' => $maxSize]);
//        }
    }
}
