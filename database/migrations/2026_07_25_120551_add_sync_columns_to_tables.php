<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tables sans aucun timestamp ──
        Schema::table('type_mesures', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('mesure_modeles', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('piece_patrons', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('annotation_patrons', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('mesures', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
        });

        // ── Tables avec seulement created_at (useCurrent) ──
        Schema::table('patrons', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::table('formes_decoupe', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::table('materiaux', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::table('regles_proportions', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        Schema::table('disposition_piece_patrons', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });

        // ── Tables métier sans softDeletes ──
        Schema::table('modele_vetements', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('commande_vetements', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('fiche_mesures', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('type_vetements', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        $tables = [
            'type_mesures', 'mesure_modeles', 'piece_patrons',
            'annotation_patrons', 'mesures',
        ];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropTimestamps();
                $t->dropSoftDeletes();
            });
        }

        $singleUpdatedAt = [
            'patrons', 'formes_decoupe', 'materiaux',
            'regles_proportions', 'disposition_piece_patrons',
        ];
        foreach ($singleUpdatedAt as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('updated_at');
                $t->dropSoftDeletes();
            });
        }

        $softDeleteOnly = [
            'modele_vetements', 'commande_vetements',
            'fiche_mesures', 'type_vetements',
        ];
        foreach ($softDeleteOnly as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropSoftDeletes();
            });
        }
    }
};
