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
        Schema::create('pdfasesorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setpdf')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('marca');
            $table->string('estado');
            $table->string('valor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};