<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->string('unit')->default('stuk'); // stuk, ml, gram, etc.
            $table->decimal('unit_price', 10, 2);
            $table->decimal('vat_rate', 5, 2)->default(21.00); // 21% of 0%
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_items');
    }
};
