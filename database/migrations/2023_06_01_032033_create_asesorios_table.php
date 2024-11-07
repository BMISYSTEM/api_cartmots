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
        Schema::create('asesorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marcas')->constrained()->onDelete('cascade');
            $table->foreignId('estados')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('descripcion');
            $table->integer('valor');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesorios');
    }
};
