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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table
                ->string('wishlist_token')
                ->default(uuid_create())
                ->index('index_wishlist_token');

            $table->tinyInteger('is_guest_cart')->default(true);
            $table->unsignedBigInteger('user_id')->nullable();

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->string('wishlist_token');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variant_id')->nullable();

            $table
                ->foreign('wishlist_token')
                ->references('wishlist_token')
                ->on('wishlists')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
