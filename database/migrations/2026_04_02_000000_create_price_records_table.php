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
        Schema::create('price_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msme_id')->constrained('msmes')->onDelete('cascade');
            $table->foreignId('commodity_id')->constrained()->onDelete('cascade');
            $table->decimal('market_price', 8, 2);
            $table->decimal('variance_percentage', 5, 2);
            $table->boolean('is_compliant');
            $table->date('recorded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_records');
    }
};
