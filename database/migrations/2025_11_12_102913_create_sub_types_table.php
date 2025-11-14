<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('product_type_id')->constrained('product_types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_types');
    }
};
