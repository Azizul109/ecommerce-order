<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderStatusUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function handle(): void
    {
        // Send email notification about status update
        // Mail::to($this->order->customer_email)->send(new OrderStatusUpdated($this->order, $this->oldStatus, $this->newStatus));

        logger("Order status updated: {$this->order->order_number} from {$this->oldStatus} to {$this->newStatus}");
    }
}
