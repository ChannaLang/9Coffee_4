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
Schema::create('product_raw_material', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('raw_material_id')->constrained()->onDelete('cascade');
    $table->enum('size', ['S', 'M', 'L'])->default('M');
    $table->decimal('quantity_required', 8, 2);
    $table->timestamps();

    // Ensure unique per product + material + size
    $table->unique(['product_id', 'raw_material_id', 'size']);
});




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_raw_material');
    }
};
