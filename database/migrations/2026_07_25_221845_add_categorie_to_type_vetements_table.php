<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('type_vetements', function (Blueprint $table) {
            $table->string('categorie')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('type_vetements', function (Blueprint $table) {
            $table->dropColumn('categorie');
        });
    }
};
