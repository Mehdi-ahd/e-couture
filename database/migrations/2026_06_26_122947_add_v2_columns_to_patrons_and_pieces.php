<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patrons', function (Blueprint $table): void {
            $table->binary('donnees_dessin_v2')->nullable()->after('donnees_dessin');
            $table->string('version_semver', 20)->nullable()->after('version');
        });

        Schema::table('piece_patrons', function (Blueprint $table): void {
            $table->binary('donnees_geometriques_v2')->nullable()->after('donnees_geometriques');
        });
    }

    public function down(): void
    {
        Schema::table('patrons', function (Blueprint $table): void {
            $table->dropColumn(['donnees_dessin_v2', 'version_semver']);
        });

        Schema::table('piece_patrons', function (Blueprint $table): void {
            $table->dropColumn('donnees_geometriques_v2');
        });
    }
};
