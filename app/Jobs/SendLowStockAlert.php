<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLowStockAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Product $product) {}

    public function handle(): void
    {
        $vendor = $this->product->vendor;

        // In a real application, you would send an email here
        // Mail::to($vendor->email)->send(new LowStockAlert($this->product));

        logger("Low stock alert for product: {$this->product->name}. Current stock: {$this->product->stock_quantity}");
    }
}
