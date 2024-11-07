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
        Schema::create('pdffinanciero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setpdf')->constrained()->onDelete('cascade');
            $table->string('placa');
            $table->string('kilometraje');
            $table->string('valor');
            $table->string('valorfinanciar');
            $table->string('mesesmanual');
            $table->string('cuotaextra');
            $table->string('tasa');
            $table->string('cuarenta');
            $table->string('sesenta');
            $table->string('setenta');
            $table->string('ochenta');
            $table->string('seguro');
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
