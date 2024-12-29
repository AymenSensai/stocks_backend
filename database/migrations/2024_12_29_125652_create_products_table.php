<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->integer('opening_stock');
            $table->integer('reorder_point');
            $table->string('category');
            $table->decimal('selling_price', 10, 2);
            $table->decimal('cost_price', 10, 2);
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Add user_id
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
