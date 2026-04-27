<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_users_external_id');
            $table->enum('type', ['COUTURIER', 'ADMINISTRATEUR', 'CLIENT']);
            $table->string('nom', 80);
            $table->string('prenom', 80);
            $table->string('email', 190)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('password');
            $table->boolean('est_actif')->default(true);

            $table->enum('kyc_type_piece', ['CNI', 'PASSEPORT', 'PERMIS_CONDUIRE'])->nullable();
            $table->enum('kyc_statut', ['NON_SOUMIS', 'EN_ATTENTE', 'VALIDE', 'REJETE'])
                ->default('NON_SOUMIS');
            $table->text('kyc_motif_rejet')->nullable();
            $table->timestamp('kyc_date_soumission')->nullable();
            $table->timestamp('kyc_date_validation')->nullable();

            $table->string('specialite', 150)->nullable();
            $table->text('adresse_atelier')->nullable();
            $table->text('bio')->nullable();

            $table->date('date_naissance')->nullable();
            $table->text('notes')->nullable();

            $table->rememberToken();
            $table->timestamps();

            $table->unique('email', 'uniq_users_email');
            $table->unique('telephone', 'uniq_users_telephone');
            $table->index('type', 'idx_users_type');
            $table->index('kyc_statut', 'idx_users_kyc_statut');
            $table->index('specialite', 'idx_users_specialite');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
