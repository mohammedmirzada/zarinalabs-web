<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A row here means the student was present. No row means absent.
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_session_id')->constrained()->cascadeOnDelete();
            $table->dateTime('checked_in_at');
            $table->timestamps();

            $table->unique(['registration_id', 'course_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
