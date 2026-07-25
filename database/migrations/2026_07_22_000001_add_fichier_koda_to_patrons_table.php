<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patrons', function (Blueprint $table): void {
            $table->longText('fichier_koda')->nullable()->after('donnees_dessin_v2');
        });
    }

    public function down(): void
    {
        Schema::table('patrons', function (Blueprint $table): void {
            $table->dropColumn('fichier_koda');
        });
    }
};
