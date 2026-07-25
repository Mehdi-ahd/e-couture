<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_event_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('mutation_id')->unique();
            $table->string('entity');
            $table->string('action');
            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_event_log');
    }
};
