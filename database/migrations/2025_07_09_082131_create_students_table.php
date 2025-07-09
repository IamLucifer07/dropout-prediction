<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_admin_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->integer('age');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->decimal('gpa', 3, 2)->nullable();
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->json('grades'); // Store subject-wise grades
            $table->string('parental_education_level')->nullable();
            $table->decimal('family_income', 10, 2)->nullable();
            $table->string('mode_of_transport')->nullable();
            $table->boolean('internet_access')->default(true);
            $table->integer('previous_failures')->default(0);
            $table->boolean('extracurricular_involvement')->default(false);
            $table->decimal('mental_health_score', 3, 2)->nullable();
            $table->integer('study_hours_per_week')->default(0);
            $table->boolean('part_time_job')->default(false);
            $table->string('living_situation')->nullable();
            $table->integer('distance_from_home')->default(0);
            $table->boolean('financial_aid')->default(false);
            $table->string('course_of_study')->nullable();
            $table->integer('semester')->default(1);
            $table->json('additional_factors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
