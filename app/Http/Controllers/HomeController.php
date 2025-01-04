<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Get dashboard data for home screen.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Aggregated data for sold quantities, purchased quantities, earnings, and spendings
        $data = DB::table('order_product')
            ->selectRaw("
                ABS(SUM(CASE WHEN quantity < 0 THEN quantity ELSE 0 END)) AS sold_quantities,
                SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) AS purchased_quantities,
                SUM(CASE WHEN quantity < 0 THEN quantity * products.selling_price ELSE 0 END) AS earnings,
                SUM(CASE WHEN quantity > 0 THEN quantity * products.cost_price ELSE 0 END) AS spendings
            ")
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->first();

        // Get total number of products
        $productCount = DB::table('products')->count();

        // Get total number of contacts
        $contactCount = DB::table('contacts')->count();

        // Sales data mapped by date
        $salesData = DB::table('order_product')
            ->selectRaw('DATE(orders.transaction_date) as date, ABS(SUM(order_product.quantity)) as sales_quantity')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->where('order_product.quantity', '<', 0)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->sales_quantity]);

        // Purchase data mapped by date
        $purchaseData = DB::table('order_product')
            ->selectRaw('DATE(orders.transaction_date) as date, SUM(order_product.quantity) as purchase_quantity')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->where('order_product.quantity', '>', 0)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->purchase_quantity]);

        // Return the data as JSON response
        return response()->json([
            'sold_quantities' => $data->sold_quantities ?? 0,
            'purchased_quantities' => $data->purchased_quantities ?? 0,
            'earnings' => $data->earnings ?? 0.0,
            'spendings' => $data->spendings ?? 0.0,
            'product_count' => $productCount,
            'contact_count' => $contactCount,
            'sales_data' => $salesData,
            'purchase_data' => $purchaseData,
        ]);
    }
}
