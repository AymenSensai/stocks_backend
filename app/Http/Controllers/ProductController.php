<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $products = Product::with(['category', 'orders'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'stock' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uploadedImage = Cloudinary::upload($image->getRealPath(), [
                'folder' => 'products',
            ]);
            $validated['image'] = $uploadedImage->getSecurePath();
        }

        $product = $request->user()->products()->create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified product.
     */
     public function show($id)
     {
         $product = Product::with(['category', 'orders'])->find($id);

         if (!$product) {
             return response()->json([
                 'message' => 'Product not found'
             ], 404);
         }

         return response()->json([
             'message' => 'Product retrieved successfully',
             'data' => $product
         ], 200);
     }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'sometimes|required|string|max:255',
            'stock' => 'sometimes|required|numeric|min:0',
            'reorder_point' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uploadedImage = Cloudinary::upload($image->getRealPath(), [
                'folder' => 'products',
            ]);
            $validated['image'] = $uploadedImage->getSecurePath();
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Request $request, $id)
    {
        $product = Product::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
