<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * GET /api/analytics/low-stock
     * Obtiene productos con bajo stock.
     */
    public function lowStock()
    {
        // whereColumn compara el valor de dos columnas en la misma fila
        $products = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock', 'asc')
            ->get();
            
        return response()->json($products);
    }
    
    /**
     * GET /api/analytics/popular
     * Obtiene los productos más vistos.
     */
    public function popularProducts()
    {
        $popularProducts = Product::where('status', 'active')
            ->orderBy('views_count', 'desc')
            ->take(10) // Tomamos el top 10
            ->get();
            
        return response()->json($popularProducts);
    }
}