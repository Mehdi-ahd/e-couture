<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiche_mesures', function (Blueprint $table) {
            $table->boolean('validee')->default(false)->after('methode');
        });
    }

    public function down(): void
    {
        Schema::table('fiche_mesures', function (Blueprint $table) {
            $table->dropColumn('validee');
        });
    }
};
