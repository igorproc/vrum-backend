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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('cart_token', 256);
            $table->set('status', ['PENDING', 'SHIPPING', 'RECEIVED']);
            $table->set('payment', ['CASH', 'CARD', 'BTC']);
            $table->timestamps();

            $table
                ->foreign('cart_token')
                ->references('cart_token')
                ->on('carts')
                ->onDelete('cascade');
        });

        Schema::create('checkout_cart', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checkout_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->integer('quantity');
            $table->timestamps();

            $table
                ->foreign('checkout_id')
                ->references('id')
                ->on('checkouts')
                ->onDelete('cascade');
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
            $table
                ->foreign('variant_id')
                ->references('id')
                ->on('product_variant_groups')
                ->onDelete('cascade');
        });

        Schema::create('checkout_user_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checkout_id');
            $table->string('name');
            $table->string('surname');
            $table->string('country');
            $table->string('city');
            $table->string('address');
            $table->string('email');
            $table->timestamps();

            $table
                ->foreign('checkout_id')
                ->references('id')
                ->on('checkouts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
        Schema::dropIfExists('checkout_user_data');
        Schema::dropIfExists('checkout_cart');
    }
};
