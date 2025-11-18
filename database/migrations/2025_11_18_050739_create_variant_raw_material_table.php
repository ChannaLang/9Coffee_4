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
    Schema::create('variant_raw_material', function (Blueprint $table) {
        $table->id();
        $table->foreignId('variant_id')->constrained()->onDelete('cascade');
        $table->foreignId('raw_material_id')->constrained()->onDelete('cascade');
        $table->decimal('quantity_required', 10, 2)->default(0); // amount used
        $table->timestamps();

        // prevent duplicate ingredient entries
        $table->unique(['variant_id', 'raw_material_id']);
    });
}

public function down()
{
    Schema::dropIfExists('variant_raw_material');
}

};
