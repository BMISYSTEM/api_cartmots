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
        Schema::create('pdfdocumentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setpdf')->constrained()->onDelete('cascade');
            $table->string('cedula');
            $table->string('solicitudcredito');
            $table->string('certificadolaboral');
            $table->string('extratos');
            $table->string('declaracion');
            $table->string('cartascomerciales');
            $table->string('facturaproveedor');
            $table->string('cartacupo');
            $table->string('camaraycomercio');
            $table->string('rut');
            $table->string('resolucionpension');
            $table->string('desprendibles');
            $table->string('certificadotradiccion');
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
