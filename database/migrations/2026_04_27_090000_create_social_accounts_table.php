<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->char('external_id', 36)->unique('uniq_social_accounts_external_id');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_user_id', 191);
            $table->string('provider_email', 190)->nullable();
            $table->text('provider_avatar_url')->nullable();
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            $table->timestamp('provider_token_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id'], 'uniq_social_accounts_provider_user');
            $table->unique(['user_id', 'provider'], 'uniq_social_accounts_user_provider');
            $table->index('provider', 'idx_social_accounts_provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
