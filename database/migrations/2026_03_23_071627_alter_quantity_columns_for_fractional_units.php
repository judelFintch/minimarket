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
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->default(0)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
};
