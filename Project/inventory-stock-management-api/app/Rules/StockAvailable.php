<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockAvailable implements ValidationRule
{
    public function __construct(
        protected int $productId,
        protected string $type
    ) {
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Stock validation only applies to OUT transactions
        |--------------------------------------------------------------------------
        */

        if ($this->type !== 'OUT') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Find active product
        |--------------------------------------------------------------------------
        */

        $product = Product::find($this->productId);

        if (!$product) {
            $fail('Produk tidak ditemukan.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Check stock availability
        |--------------------------------------------------------------------------
        */

        if ((int) $value > $product->stock) {
            $fail(
                "Stok produk {$product->name} tidak mencukupi. "
                . "Tersedia: {$product->stock}, diminta: {$value}."
            );
        }
    }
}