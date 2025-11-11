<?php

declare(strict_types = 1);

use App\Enums\AlertTypes;
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
        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('alert_type', array_column(AlertTypes::cases(), 'value'))->nullable();
            $table->boolean('notifications')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('alert_channels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
