<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('college_admin_id')->constrained()->onDelete('cascade');
            $table->enum('prediction_result', ['dropout', 'at_risk', 'safe']);
            $table->decimal('confidence_score', 5, 4);
            $table->json('feature_importance')->nullable();
            $table->json('model_metadata')->nullable();
            $table->string('model_version')->default('1.0');
            $table->json('input_data');
            $table->timestamp('predicted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
