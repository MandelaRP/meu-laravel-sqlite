<?php

declare(strict_types = 1);

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('volume_transacionado', 15, 2)->default(0.00);
            $table->decimal('approved_deposits', 15, 2)->default(0.00);
            $table->decimal('approved_deposits_net', 15, 2)->default(0.00);
            $table->decimal('profit_for_platform', 15, 2)->default(0.00);
            $table->decimal('value_paid_in_taxes', 15, 2)->default(0.00);
            $table->decimal('withdraw_amount', 15, 2)->default(0.00);
            $table->decimal('deposit_amount', 15, 2)->default(0.00);
            $table->decimal('average_monthly_income', 15, 2)->default(0.00);
            $table->string('person_type')->nullable();
            $table->string('full_name')->nullable();
            $table->string('document')->nullable();
            $table->string('average_revenue')->nullable();
            $table->string('average_ticket')->nullable();
            $table->string('products')->nullable();
            $table->string('social_reason')->nullable();
            $table->string('social_contract')->nullable();
            $table->string('rg_cnh_frente')->nullable();
            $table->string('rg_cnh_verso')->nullable();
            $table->string('selfie')->nullable();
            $table->enum('role', array_column(UserRoleEnum::cases(), 'value'))->default(UserRoleEnum::USER->value);
            $table->enum('status', array_column(UserStatusEnum::cases(), 'value'))->default(UserStatusEnum::RECENT_USER->value);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
