<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('active_ingredient')->nullable();
            $table->string('dosage_form')->nullable(); // Tablet, injectie, zalf, etc.
            $table->string('strength')->nullable();
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->string('stock_unit')->default('stuks'); // stuks, ml, gram
            $table->decimal('min_stock_level', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('supplier')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->boolean('prescription_required')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
