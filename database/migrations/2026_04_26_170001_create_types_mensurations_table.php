<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_mensurations', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_types_mensurations_external_id');
            $table->string('code', 50);
            $table->string('nom', 120);
            $table->string('unite', 10)->default('cm');
            $table->enum('categorie', ['PRINCIPALE', 'SECONDAIRE']);
            $table->text('description')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();

            $table->unique('code', 'uniq_types_mensurations_code');
            $table->index('est_actif', 'idx_types_mensurations_actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_mensurations');
    }
};
