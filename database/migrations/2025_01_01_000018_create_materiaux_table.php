<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materiaux', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('image_url')->nullable();
            $table->boolean('est_global')->default(false);
            $table->foreignId('forme_decoupe_id')
                ->nullable()
                ->constrained('formes_decoupe')
                ->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiaux');
    }
};
