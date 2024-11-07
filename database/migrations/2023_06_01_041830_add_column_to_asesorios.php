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
        Schema::table('asesorios', function (Blueprint $table) {
            $table->string('foto1');
            $table->string('foto2');
            $table->string('foto3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesorios', function (Blueprint $table) {
            //
        });
    }
};
