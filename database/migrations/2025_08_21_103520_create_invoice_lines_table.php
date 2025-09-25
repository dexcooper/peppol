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
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Invoice::class)->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->bigInteger('unit_price')->nullable();
            $table->integer('number')->nullable();
            $table->bigInteger('total_amount');
            $table->decimal('vat_rate', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
