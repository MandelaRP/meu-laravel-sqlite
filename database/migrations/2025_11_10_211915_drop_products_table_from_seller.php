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
        // Remover foreign keys primeiro
        Schema::table('checkouts', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        
        // Dropar a tabela products
        Schema::dropIfExists('products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recriar a tabela products (se necessário reverter)
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignUuid('category_id')->nullable()->constrained('categories');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('type', ['FISICAL', 'DIGITAL'])->default('DIGITAL');
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });
        
        // Recriar foreign keys
        Schema::table('checkouts', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
        });
    }
};
