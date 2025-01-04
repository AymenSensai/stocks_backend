<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Contact;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index()
    {
        $orders = Order::with(['contact', 'products'])->get();

        return response()->json([
            'message' => 'Orders retrieved successfully!',
            'data' => $orders,
        ]);
    }

    // Store a newly created order
    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'nullable|exists:contacts,id',
            'transaction_date' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        // Create the order
        $order = Order::create([
            'contact_id' => $request->contact_id,
            'transaction_date' => $request->transaction_date,
            'notes' => $request->notes,
        ]);

        // Attach products and adjust stock
        foreach ($request->products as $product) {
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
            ]);

            // Update product stock
            $productModel = Product::find($product['id']);
            $productModel->stock += $product['quantity']; // Add positive or subtract negative quantity
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

        // Sync products and adjust stock
        $products = [];
        foreach ($request->products as $product) {
            $products[$product['id']] = ['quantity' => $product['quantity']];

            $productModel = Product::find($product['id']);
            $productModel->stock += $product['quantity']; // Apply new stock change
            $productModel->save();
        }

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
