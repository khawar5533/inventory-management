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
        Schema::create('inventory_movements', function (Blueprint $table) {
             $table->id();
            $table->unsignedBigInteger('lot_id');
            $table->string('batch_number');
            $table->enum('type', ['check-in', 'check-out']);
            $table->integer('quantity');
            $table->datetime('movement_date');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('lot_id')->references('id')->on('product_lots')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');

            // Unique constraint to prevent duplicate entries
            $table->unique(['lot_id', 'batch_number', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
