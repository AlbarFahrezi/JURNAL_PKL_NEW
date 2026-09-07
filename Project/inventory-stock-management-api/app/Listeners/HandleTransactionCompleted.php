<?php

namespace App\Listeners;

use App\Events\TransactionCompleted;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\TransactionLog;

class HandleTransactionCompleted
{
    public function handle(TransactionCompleted $event): void
    {
        $transaction = $event->transaction;

        $transaction->load('details');

        /*
         * Get and lock all products required by this transaction.
         *
         * lockForUpdate() ensures stock validation and stock update
         * happen safely inside the same database transaction.
         */
        $productIds = $transaction->details
            ->pluck('product_id')
            ->unique()
            ->values();

        $products = Product::whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        /*
         * Validate that all products still exist.
         */
        foreach ($transaction->details as $detail) {
            if (!$products->has($detail->product_id)) {
                throw new \RuntimeException(
                    "Product with ID {$detail->product_id} not found."
                );
            }
        }

        /*
         * Validate stock for OUT transaction.
         *
         * No stock is changed if this validation fails.
         */
        if ($transaction->type === 'OUT') {
            foreach ($transaction->details as $detail) {
                $product = $products->get($detail->product_id);

                if ($product->stock < $detail->quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for product "
                        . "{$product->name}. Available: "
                        . "{$product->stock}, required: "
                        . "{$detail->quantity}."
                    );
                }
            }
        }

        /*
         * Update stock and create stock history.
         */
        foreach ($transaction->details as $detail) {
            $product = $products->get($detail->product_id);

            $stockBefore = $product->stock;

            if ($transaction->type === 'IN') {
                $stockAfter =
                    $stockBefore + $detail->quantity;

                $historyType = 'IN';
            } else {
                $stockAfter =
                    $stockBefore - $detail->quantity;

                $historyType = 'OUT';
            }

            $product->update([
                'stock' => $stockAfter,
            ]);

            StockHistory::create([
                'product_id' => $product->id,
                'type' => $historyType,
                'quantity' => $detail->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'note' =>
                    'Stock updated from transaction '
                    . $transaction->transaction_code,
            ]);
        }

        /*
         * Change transaction status.
         */
        $oldStatus = $transaction->status;

        $transaction->update([
            'status' => 'completed',
        ]);

        /*
         * Create audit trail.
         */
        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'user_id' => $event->userId,
            'old_status' => $oldStatus,
            'new_status' => 'completed',
            'note' =>
                'Transaction completed and stock updated by event listener.',
        ]);
    }
}