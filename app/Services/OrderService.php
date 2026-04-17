<?php
namespace App\Services;

use App\DTO\OrderData;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * @throws \Exception
     */
    public function createOrder(OrderData $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            $order = new Order(['customer_id' => $orderData->customerId, 'status' => Order::STATUS_NEW]);
            $order->save();

            $totalAmount = 0;

            foreach ($orderData->items as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData->productId);

                if ($product->stock_quantity < $itemData->quantity) {
                    throw new \Exception("Недостаточно товара '{$product->name}' на складе");
                }

                $product->decrement('stock_quantity', $itemData->quantity);

                $unitPrice = $product->price;
                $itemTotal = $unitPrice * $itemData->quantity;
                $totalAmount += $itemTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items.product');
        });
    }

    public function changeStatus(Order $order, string $newStatus): Order
    {
        $allowedTransitions = [
            Order::STATUS_NEW => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
            Order::STATUS_CONFIRMED => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_SHIPPED],
            Order::STATUS_SHIPPED => [Order::STATUS_COMPLETED],
            Order::STATUS_COMPLETED => [],
            Order::STATUS_CANCELLED => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$order->status] ?? [])) {
            throw new \Exception("Недопустимый переход статуса с '{$order->status}' на '{$newStatus}'");
        }

        $order->status = $newStatus;

        if ($newStatus === Order::STATUS_CONFIRMED) {
            $order->confirmed_at = now();
        } elseif ($newStatus === Order::STATUS_SHIPPED) {
            $order->shipped_at = now();
        }

        $order->save();

        return $order;
    }
}