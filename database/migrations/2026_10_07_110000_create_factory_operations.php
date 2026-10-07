<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_setups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('kind');
            $table->string('code');
            $table->string('name');
            $table->json('details');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'kind', 'code']);
        });
        Schema::create('factory_production', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->date('date');
            $table->string('factory_code');
            $table->string('line_code');
            $table->string('order_ref');
            $table->string('style');
            $table->unsignedInteger('target');
            $table->unsignedInteger('completed');
            $table->unsignedInteger('rejected');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            $table->unique(['company_id', 'date', 'factory_code', 'line_code', 'order_ref'], 'factory_daily_order_unique');
        });
        Schema::create('factory_safety', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->date('date');
            $table->string('factory_code');
            $table->string('category');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('owner');
            $table->string('status');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_safety');
        Schema::dropIfExists('factory_production');
        Schema::dropIfExists('factory_setups');
    }
};
