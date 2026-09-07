<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_code')->unique();

            $table->foreignId('user_id')
                  ->constrained()
                  ->restrictOnDelete();

            $table->enum('type', [
                'IN',
                'OUT'
            ]);

            $table->enum('status', [
                'draft',
                'completed',
                'cancelled'
            ])->default('draft');

            $table->decimal('total', 14, 2)->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};