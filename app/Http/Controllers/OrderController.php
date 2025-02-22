<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Contact;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::with(['contact', 'products'])
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'asc')
            ->get();

        return response()->json([
            'message' => 'Orders retrieved successfully!',
            'data' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'nullable|exists:contacts,id',
            'transaction_date' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $order = $request->user()->orders()->create([
            'contact_id' => $validated['contact_id'],
            'transaction_date' => $validated['transaction_date'],
            'notes' => $validated['notes'],
        ]);

        foreach ($validated['products'] as $product) {
            // Attach product to the order with the price field
            $productModel = Product::find($product['id']);
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'price' => $productModel->selling_price, // Use the product's price here
            ]);

            // Update stock
            $productModel->stock += $product['quantity'];
            $productModel->save();
        }

        return response()->json(['message' => 'Order created successfully!', 'order' => $order]);
    }


    // Update the specified order
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'contact_id' => 'nullable|exists:contacts,id',
            'transaction_date' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        // Adjust stock for old products
        foreach ($order->products as $oldProduct) {
            $productModel = Product::find($oldProduct->id);
            $productModel->stock -= $oldProduct->pivot->quantity; // Revert previous stock change
            $productModel->save();
        }

        // Update order details
        $order->update([
            'contact_id' => $request->contact_id,
            'transaction_date' => $request->transaction_date,
            'notes' => $request->notes,
        ]);

        // Prepare products and sync them with price data
        $products = [];
        foreach ($request->products as $product) {
            $productModel = Product::find($product['id']);
            $products[$product['id']] = [
                'quantity' => $product['quantity'],
                'price' => $productModel->selling_price, // Use the product's price here
            ];

            // Adjust stock
            $productModel->stock += $product['quantity']; // Apply new stock change
            $productModel->save();
        }

        // Sync the products with the order, including the price
        $order->products()->sync($products);

        return response()->json(['message' => 'Order updated successfully!', 'order' => $order]);
    }

    public function destroy(Order $order)
    {
        // Optional: Check if the order exists (this will happen automatically with route model binding)
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Delete the order (this will automatically remove the related products in the pivot table)
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully!']);
    }
}
