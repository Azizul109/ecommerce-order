<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->isVendor()) {
            $orders = $this->orderService->getVendorOrders($user->id);
        } else {
            $orders = $this->orderService->getCustomerOrders($user->id);
        }

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function store(OrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order,
        ], Response::HTTP_CREATED);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json([
            'data' => $order->load('items.product', 'items.variant'),
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = $this->orderService->updateOrderStatus($order, $request->status);

        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => $order,
        ]);
    }

    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        if (!$order->canBeCancelled()) {
            return response()->json([
                'error' => 'Order cannot be cancelled at this stage',
            ], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderService->updateOrderStatus($order, 'cancelled');

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => $order,
        ]);
    }
}
