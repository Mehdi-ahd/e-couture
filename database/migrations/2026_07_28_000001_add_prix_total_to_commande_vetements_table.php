<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commande_vetements', function (Blueprint $table) {
            $table->decimal('prix_total', 10, 2)->nullable()->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('commande_vetements', function (Blueprint $table) {
            $table->dropColumn('prix_total');
        });
    }
};
