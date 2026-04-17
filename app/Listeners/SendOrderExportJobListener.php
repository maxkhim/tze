<?php
namespace App\Listeners;

use App\Events\OrderStatusChangedEvent;
use App\Jobs\ExportOrderJob;
use App\Models\Order;

class SendOrderExportJobListener
{
    public function handle(OrderStatusChangedEvent $event): void
    {
        if ($event->order->status === Order::STATUS_CONFIRMED) {
            ExportOrderJob::dispatch($event->order);
        }
    }
}