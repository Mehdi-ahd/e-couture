<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes_couturiers', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_notes_couturiers_external_id');
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('couturier_id')->constrained('users');
            $table->foreignId('commande_id')->nullable()->constrained('commandes_vetements')->nullOnDelete();
            $table->unsignedTinyInteger('note_service');
            $table->unsignedTinyInteger('note_conception');
            $table->unsignedTinyInteger('note_livraison');
            $table->unsignedTinyInteger('note_delai');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_notation')->useCurrent();
            $table->boolean('est_visible')->default(true);
            $table->timestamps();

            $table->unique(['client_id', 'commande_id'], 'uniq_notes_couturiers_client_commande');
            $table->index(['couturier_id', 'est_visible'], 'idx_notes_couturiers_couturier_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes_couturiers');
    }
};
