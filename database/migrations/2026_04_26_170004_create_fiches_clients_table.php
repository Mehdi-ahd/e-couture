<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiches_clients', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_fiches_clients_external_id');
            $table->foreignId('couturier_id')->constrained('users');
            $table->foreignId('client_id')->constrained('users');
            $table->timestamp('date_creation')->useCurrent();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();

            $table->unique(['couturier_id', 'client_id'], 'uniq_fiches_clients_couple');
            $table->index(['couturier_id', 'est_actif'], 'idx_fiches_clients_couturier_actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_clients');
    }
};
