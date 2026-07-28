<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone');
            $table->string('email')->nullable();
            $table->string('genre')->nullable(); // homme, femme, autre
            $table->date('date_naissance')->nullable();
            $table->text('adresse')->nullable();
            $table->string('photo_url')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // date_creation kept for compatibility with previous `fiches_clients`
            $table->timestamp('date_creation')->useCurrent();
            $table->boolean('est_actif')->default(true);
            $table->foreignId('prestataire_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->softDeletes();
            $table->index(['prestataire_id', 'est_actif'], 'idx_clients_prestataire');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
