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
            $table->uuid('external_id')->unique();
            $table->foreignId('couturier_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();
            $table->timestamp('date_creation')->useCurrent();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['couturier_id', 'client_id'], 'uniq_fiches_clients_couple');
            $table->index(['couturier_id', 'est_actif'], 'idx_fiches_couturier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_clients');
    }
};
