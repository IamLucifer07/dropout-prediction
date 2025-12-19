<?php

namespace Tests\Feature;

use App\Models\CollegeAdmin;
use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PredictionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_creation_triggers_prediction_record(): void
    {
        Http::fake([
            config('services.ml.url') => Http::response([
                'prediction' => 'dropout',
                'confidence' => 0.82,
                'probabilities' => [
                    'dropout' => 0.82,
                    'at_risk' => 0.1,
                    'safe' => 0.08,
                ],
                'feature_importance' => [
                    ['feature' => 'attendance_rate', 'importance' => 0.32],
                    ['feature' => 'gpa', 'importance' => 0.28],
                ],
                'model_metadata' => [
                    'model_path' => 'random_forest.joblib',
                    'feature_schema_version' => ['version' => 'test'],
                    'available_classes' => ['dropout', 'at_risk', 'safe'],
                ],
            ], 200),
        ]);

        $admin = CollegeAdmin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'college_name' => 'Example College',
        ]);

        $payload = [
            'full_name' => 'Student Example',
            'age' => 20,
            'gender' => 'female',
            'gpa' => 3.2,
            'attendance_rate' => 85,
            'grades' => [
                ['subject' => 'Math', 'grade' => 'A'],
                ['subject' => 'Physics', 'grade' => 'B+'],
            ],
            'parental_education_level' => 'secondary',
            'family_income' => 28000,
            'mode_of_transport' => 'bus',
            'internet_access' => true,
            'previous_failures' => 1,
            'extracurricular_involvement' => true,
            'mental_health_score' => 6.5,
            'study_hours_per_week' => 18,
            'part_time_job' => false,
            'living_situation' => 'with_family',
            'distance_from_home' => 12,
            'financial_aid' => false,
            'course_of_study' => 'Computer Science',
            'semester' => 3,
            'additional_factors' => [
                ['key' => 'mentor_program', 'value' => 'enrolled'],
            ],
        ];

        $response = $this
            ->actingAs($admin, 'web')
            ->postJson('/api/students', $payload);

        $response->assertCreated();

        $studentId = $response->json('student.id');

        $this->assertNotNull($studentId, 'Student id should be present in response');

        $this->assertDatabaseHas('predictions', [
            'student_id' => $studentId,
            'prediction_result' => 'dropout',
            'model_version' => 'random_forest.joblib',
        ]);

        $this->assertCount(1, Prediction::all());
    }

    public function test_fallback_prediction_is_used_when_ml_service_fails(): void
    {
        Http::fake([
            config('services.ml.url') => Http::response([], 500),
        ]);

        $admin = CollegeAdmin::create([
            'name' => 'Fallback Admin',
            'email' => 'fallback@example.com',
            'password' => Hash::make('password'),
            'college_name' => 'Fallback College',
        ]);

        $payload = [
            'full_name' => 'Fallback Student',
            'age' => 22,
            'gender' => 'male',
            'attendance_rate' => 55,
            'grades' => [],
            'internet_access' => false,
            'previous_failures' => 4,
            'extracurricular_involvement' => false,
            'mental_health_score' => 3,
            'study_hours_per_week' => 5,
            'part_time_job' => true,
            'distance_from_home' => 100,
            'financial_aid' => true,
            'semester' => 2,
        ];

        $response = $this
            ->actingAs($admin, 'web')
            ->postJson('/api/students', $payload);

        $response->assertCreated();

        $prediction = Prediction::first();
        $this->assertNotNull($prediction, 'A fallback prediction should be stored');
        $this->assertSame('fallback', $prediction->model_version);
    }
}
