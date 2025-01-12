<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessProductsJob;
use App\Jobs\UpdateProductsJob;

class ProductController extends Controller
{
    public function index()
    {
        $cacheKey = 'products_list';
        $cacheTTL = now()->addMinutes(10);

        $products = Cache::remember($cacheKey, $cacheTTL, function () {
            return Product::all();
        });

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'products' => 'required|array|max:100',
            'products.*.product_name' => 'required|string|max:255',
            'products.*.unit_value' => 'required|numeric|min:0',
            'products.*.product_description' => 'nullable|string',
            'products.*.avaliable_quantity' => 'nullable|max:255',
            'products.*.SKU' => 'required|string|unique:products,SKU'
        ]);
    
        ProcessProductsJob::dispatch($validatedData['products']);
    
        Cache::forget('products_list');
        
        return response()->json(['message' => 'Lote de produtos em processamento.'], 202);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.name' => 'nullable|string|max:255',
            'products.*.unity_price' => 'nullable|numeric|min:0',
            'products.*.description' => 'nullable|string',
            'products.*.available_quantity' => 'nullable|numeric|min:0',
        ]);

        $productData = collect($validatedData['products'])->map(function ($product) {
            return collect($product)->only(['name', 'unity_price', 'description', 'available_quantity']);
        });

        $productIds = collect($validatedData['products'])->pluck('product_id')->toArray();

        UpdateProductsJob::dispatch($productIds, $productData);

        return response()->json(['message' => 'Atualizações em andamento.'], 202);
    }


    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        Cache::forget('products_list');
        return response()->json(['message' => 'Product deleted']);
    }
}
