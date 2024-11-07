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
        Schema::create('pdfmatricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setpdf')->constrained()->onDelete('cascade');
            $table->string('traspasos');
            $table->string('honorarios');
            $table->string('impuestos');
            $table->string('pignoracion');
            $table->string('certificadotradiccion');
            $table->string('siginperitaje');
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
