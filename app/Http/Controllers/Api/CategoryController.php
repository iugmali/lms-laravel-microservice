<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    private $validation_rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];

    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->validation_rules);
        return Category::create($request->all());
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $this->validate($request, $this->validation_rules);
        $category->update($request->all());
        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent(); // 204
    }
}
