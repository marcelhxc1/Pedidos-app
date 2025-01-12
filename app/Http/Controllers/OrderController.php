<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use App\Jobs\CreateOrder;
use App\Models\User;
use App\Jobs\SendOrderStatusUpdateEmail;

class OrderController extends Controller
{
    public function index()
    {
        $cacheKey = 'orders_list';
        $cacheTTL = now()->addMinutes(10); // Tempo de vida do cache (10 minutos)

        $orders = Cache::remember($cacheKey, $cacheTTL, function () {
            return Order::with('products')->get();
        });

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $user = User::find($request->user_id);
        $products = $request->input('products');

        CreateOrder::dispatch($user, $products);

        return response()->json([
            'message' => 'Pedido recebido e sendo processado.',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $order->status = $validated['status'];
        $order->save();

        SendOrderStatusUpdateEmail::dispatch($order);

        return response()->json([
            'message' => 'Status atualizado e e-mail enviado com sucesso.',
            'order' => $order,
        ]);

    }
}
