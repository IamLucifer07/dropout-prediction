<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Prediction;

class PredictionSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $riskLevels = ['safe', 'at_risk', 'dropout'];

        foreach ($students as $student) {
            Prediction::create([
                'student_id' => $student->id,
                'college_admin_id' => $student->college_admin_id,
                'prediction_result' => $riskLevels[array_rand($riskLevels)],
                'confidence_score' => fake()->randomFloat(2, 0.5, 1),
                'model_version' => 'seeded_data_v1.0',
                // Create predictions over the last 6 months for trend chart
                'created_at' => fake()->dateTimeBetween('-6 months'),
                'predicted_at' => fake()->dateTimeBetween('-6 months'),
                'input_data' => ['seeded' => true],
            ]);
        }
    }
}
