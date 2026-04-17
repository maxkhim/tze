<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ExportOrderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(private Order $order) {}

    public function handle(): void
    {
        $url = config('services.export.url', 'https://httpbin.org/post');

        // Подготовка данных для отправки
        $requestPayload = [
            'order_id' => $this->order->id,
            'customer_email' => $this->order->customer->email,
            'total_amount' => $this->order->total_amount,
            'items' => $this->order->items->map(fn($item) => [
                'product_sku' => $item->product->sku,
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
            ])->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        // Создаём запись в order_exports со статусом pending
        $exportLog = OrderExport::query()->create([
            'order_id' => $this->order->id,
            'status' => OrderExport::STATUS_PENDING,
            'request_data' => $requestPayload,
            'attempted_at' => now(),
        ]);

        try {
            $response = Http::timeout(10)->post($url, $requestPayload);

            $exportLog->response_data = [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?: $response->body(),
            ];

            if ($response->successful()) {
                $exportLog->status = OrderExport::STATUS_SUCCESS;
            } else {
                $exportLog->status = OrderExport::STATUS_FAILED;
                $exportLog->error_message = 'HTTP Error: ' . $response->status();
            }
        } catch (\Exception $e) {
            $exportLog->status = OrderExport::STATUS_FAILED;
            $exportLog->error_message = $e->getMessage();
        } finally {
            $exportLog->completed_at = now();
            $exportLog->save();
        }

        // Если статус failed, выбрасываем исключение для повторной попытки (если попытки остались)
        if ($exportLog->status === OrderExport::STATUS_FAILED) {
            throw new \Exception($exportLog->error_message ?? 'Export failed');
        }

        if (!$response->successful()) {
            throw new \Exception('Export failed: ' . $response->body());
        }
    }
}