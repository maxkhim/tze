<?php

namespace Tests\Feature;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;

class OrderCreationTest extends TestCase
{
    public function test_successful_order_creation(): void
    {
        //Http::fake();

        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/api/v1/orders', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ],
            ],
        ]);

        $this->assertEquals(
            201,
            $response->status(),
            "Создание через заказа через эндпоинт выполнено");

        $response
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'total_amount',
                    'items' => [
                        ['product_id', 'quantity', 'unit_price', 'total_price']
                    ]
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => Order::STATUS_NEW,
            'total_amount' => 200.00,
        ]);

        $this->assertEquals(
            8,
            $product->fresh()->stock_quantity,
            "Заказ создан, остатки актуализированы"
        );
    }

    public function test_successful_order_confirmed(): void
    {
        $order = Order::query()->latest("created_at")->first();

        $response = $this->patchJson(
            sprintf('/api/v1/orders/%d/status', $order->id),
            [
                'status' => Order::STATUS_CONFIRMED
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CONFIRMED
        ]);

    }


}