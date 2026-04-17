<?php

namespace App\Http\Controllers\Api\V1;


use App\DTO\OrderData;
use App\DTO\OrderItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateOrderRequest;
use App\Http\Requests\Api\V1\UpdateOrderStatusRequest;
use App\Http\Resources\Api\V1\OrderCollection;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function store(CreateOrderRequest $request): OrderResource
    {
        $items = array_map(
            fn($item) => new OrderItemData($item['product_id'], $item['quantity']),
            $request->validated('items')
        );

        $orderData = new OrderData($request->validated('customer_id'), $items);

        $order = $this->orderService->createOrder($orderData);

        return new OrderResource($order);
    }

    public function index(Request $request): OrderCollection
    {
        $orders = Order::query()
            ->with(['customer', 'items.product'])
            ->byStatus($request->query('status'))
            ->byCustomer($request->query('customer_id'))
            ->byDateRange($request->query('from'), $request->query('to'))
            ->latest()
            ->paginate(20);

        return new OrderCollection($orders);
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'items.product']);
        return new OrderResource($order);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $updatedOrder = $this->orderService->changeStatus($order, $request->validated('status'));
        return new OrderResource($updatedOrder->load(['customer', 'items.product']));
    }
}