<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateOrderInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        // Generate PDF invoice
        // In a real application, you would use a PDF library like Dompdf
        $invoiceData = [
            'order' => $this->order,
            'items' => $this->order->items,
        ];

        // Store PDF in storage
        // $pdf = PDF::loadView('invoices.order', $invoiceData);
        // Storage::put("invoices/{$this->order->order_number}.pdf", $pdf->output());

        logger("Invoice generated for order: {$this->order->order_number}");
    }
}
