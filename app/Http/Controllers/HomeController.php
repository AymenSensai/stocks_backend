<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Get dashboard data for home screen.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Aggregated data for sold quantities, purchased quantities, earnings, and spendings for a specific user
        $data = DB::table('order_product')
            ->selectRaw("
                ABS(SUM(CASE WHEN quantity < 0 THEN quantity ELSE 0 END)) AS sold_quantities,
                SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) AS purchased_quantities,
                SUM(CASE WHEN quantity < 0 THEN quantity * products.selling_price ELSE 0 END) AS earnings,
                SUM(CASE WHEN quantity > 0 THEN quantity * products.cost_price ELSE 0 END) AS spendings
            ")
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->where('products.user_id', $userId)
            ->first();

        // Get total number of products for the user
        $productCount = DB::table('products')
            ->where('user_id', $userId)
            ->count();

        // Get total number of contacts for the user
        $contactCount = DB::table('contacts')
            ->where('user_id', $userId)
            ->count();

        // Sales data mapped by date for the user
        $salesData = DB::table('order_product')
            ->selectRaw('DATE(orders.transaction_date) as date, ABS(SUM(order_product.quantity)) as sales_quantity')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->where('order_product.quantity', '<', 0)
            ->where('products.user_id', $userId)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->sales_quantity])
            ->all(); // Convert collection to array

        // Ensure salesData is not an empty array
        $salesData = !empty($salesData) ? $salesData : (object) [];

        // Purchase data mapped by date for the user
        $purchaseData = DB::table('order_product')
            ->selectRaw('DATE(orders.transaction_date) as date, SUM(order_product.quantity) as purchase_quantity')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->where('order_product.quantity', '>', 0)
            ->where('products.user_id', $userId)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->purchase_quantity])
            ->all(); // Convert collection to array

        // Ensure purchaseData is not an empty array
        $purchaseData = !empty($purchaseData) ? $purchaseData : (object) [];

        // Return the data as JSON response
        return response()->json([
            'sold_quantities' => $data->sold_quantities ?? 0,
            'purchased_quantities' => $data->purchased_quantities ?? 0,
            'earnings' => $data->earnings ?? "0",
            'spendings' => $data->spendings ?? "0",
            'product_count' => $productCount,
            'contact_count' => $contactCount,
            'sales_data' => $salesData,
            'purchase_data' => $purchaseData,
        ]);
    }
}
