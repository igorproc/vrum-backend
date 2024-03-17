<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->set('__typename', ['BASE', 'CONFIGURABLE']);
            $table->string('name', 64)->unique();
            $table->string('description', 512);
            $table->float('price', 10);
            $table->string('product_image', 128);
            $table->unsignedBigInteger('brand_id');
            $table->timestamps();
            $table
                ->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->onDelete('cascade');
        });

        Schema::create('product_option_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('label', 32);
            $table->timestamps();

            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });

        Schema::create('product_option_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_option_group_id');
            $table->string('label', 32);
            $table->string('value', 32);
            $table->timestamps();

            $table
                ->foreign('product_option_group_id')
                ->references('id')
                ->on('product_option_groups')
                ->onDelete('cascade');
        });

        Schema::create('product_variant_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku', 32);
            $table->string('image_url', 128);
            $table->timestamps();

            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });

        Schema::create('product_variant_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_group_id');
            $table->unsignedInteger('option_group_id');
            $table->unsignedInteger('option_item_id');

            $table
                ->foreign('product_variant_group_id')
                ->references('id')
                ->on('product_variant_groups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
