<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_mesures', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('code')->unique();
            $table->string('nom');
            $table->string('unite');
            $table->string('categorie');
            $table->text('description');
            $table->boolean('est_actif')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_mesures');
    }
};
