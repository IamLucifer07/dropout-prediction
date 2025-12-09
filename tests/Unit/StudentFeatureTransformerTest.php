<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Services\StudentFeatureTransformer;
use Tests\TestCase;

class StudentFeatureTransformerTest extends TestCase
{
    public function test_it_transforms_student_attributes_into_feature_payload(): void
    {
        $student = new Student([
            'full_name' => 'Case Student',
            'age' => 21,
            'gender' => 'female',
            'gpa' => null,
            'attendance_rate' => 88,
            'grades' => [
                ['subject' => 'Algebra', 'grade' => 'A'],
                ['subject' => 'Physics', 'grade' => 'B+'],
            ],
            'parental_education_level' => 'Bachelors',
            'family_income' => 30000,
            'mode_of_transport' => 'Car',
            'internet_access' => true,
            'previous_failures' => 0,
            'extracurricular_involvement' => true,
            'mental_health_score' => 7,
            'study_hours_per_week' => 12,
            'part_time_job' => false,
            'living_situation' => 'with_family',
            'distance_from_home' => 8,
            'financial_aid' => false,
            'course_of_study' => 'Information Technology',
            'semester' => 4,
        ]);

        $transformer = new StudentFeatureTransformer();
        $features = $transformer->transform($student);

        $this->assertSame(21, $features['age']);
        $this->assertSame('female', $features['gender']);
        $this->assertArrayHasKey('gpa', $features);
        $this->assertGreaterThan(0, $features['gpa']);
        $this->assertSame(88.0, $features['attendance_rate']);
        $this->assertSame('bachelors', $features['parental_education_level']);
        $this->assertSame('course_information_technology', $features['course_of_study']);
        $this->assertGreaterThan(80, $features['grades_average']);
        $this->assertSame(2.0, $features['grades_count']);
    }
}
