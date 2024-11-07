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
        Schema::create('pdfretoma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setpdf')->constrained()->onDelete('cascade');
            $table->string('placa');
            $table->string('marca');
            $table->string('refvehiculo');
            $table->string('modelo');
            $table->string('kilometraje');
            $table->string('valorcomercial');
            $table->string('descripcion');
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
