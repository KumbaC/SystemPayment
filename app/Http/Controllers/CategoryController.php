<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('pages.categories.index', [
            'title' => 'Categorías',
            'categories' => Category::query()->withCount('products')->latest()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Category::query()->create($data + ['active' => true]);

        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['boolean'],
        ]);

        $category->update($data + ['active' => $request->boolean('active', true)]);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Categoría eliminada.');
    }
}
