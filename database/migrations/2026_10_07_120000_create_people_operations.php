<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('kind', 20);
            $table->string('employee_full_id')->nullable();
            $table->string('title');
            $table->date('start_date');
            $table->date('due_date')->nullable();
            $table->string('status', 30);
            $table->json('details');
            $table->unsignedBigInteger('recorded_by');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->index(['company_id', 'kind', 'status']);
            $table->index(['company_id', 'employee_full_id']);
        });
        Schema::create('hr_record_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_record_id')->constrained('hr_records')->cascadeOnDelete();
            $table->string('employee_full_id');
            $table->unique(['hr_record_id', 'employee_full_id']);
            $table->index('employee_full_id');
        });
        Schema::create('hr_record_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_record_id')->constrained('hr_records')->cascadeOnDelete();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_name');
            $table->string('action', 30);
            $table->string('status', 30);
            $table->unsignedInteger('version');
            $table->timestamp('created_at');
        });
        Schema::create('hr_record_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_record_id')->constrained('hr_records')->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_record_files');
        Schema::dropIfExists('hr_record_events');
        Schema::dropIfExists('hr_record_participants');
        Schema::dropIfExists('hr_records');
    }
};
