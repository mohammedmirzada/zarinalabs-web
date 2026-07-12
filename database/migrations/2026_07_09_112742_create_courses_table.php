<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('video_url')->nullable();
            $table->string('type');
            $table->string('category');
            $table->string('level');
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('format');
            $table->string('meeting_link')->nullable();  // required when format = online
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();  // required when format = offline
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('capacity');
            $table->date('registration_deadline');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
