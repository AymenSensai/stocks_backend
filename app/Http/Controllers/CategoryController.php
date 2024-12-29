<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for the authenticated user.
     */
    public function index()
    {
        $categories = auth()->user()->categories;

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ], 200);
    }

    /**
     * Store a newly created category for the authenticated user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = auth()->user()->categories()->create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $category = auth()->user()->categories()->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'message' => 'Category retrieved successfully',
            'data' => $category,
        ], 200);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = auth()->user()->categories()->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category,
        ], 200);
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = auth()->user()->categories()->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}
