<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

class StudentFeatureTransformer
{
    private const LETTER_GRADE_POINTS = [
        'A+' => 4.0,
        'A' => 4.0,
        'A-' => 3.7,
        'B+' => 3.3,
        'B' => 3.0,
        'B-' => 2.7,
        'C+' => 2.3,
        'C' => 2.0,
        'C-' => 1.7,
        'D+' => 1.3,
        'D' => 1.0,
        'D-' => 0.7,
        'E' => 0.5,
        'F' => 0.0,
    ];

    private const LETTER_GRADE_PERCENTAGES = [
        'A+' => 98,
        'A' => 95,
        'A-' => 92,
        'B+' => 88,
        'B' => 85,
        'B-' => 82,
        'C+' => 78,
        'C' => 75,
        'C-' => 72,
        'D+' => 68,
        'D' => 65,
        'D-' => 62,
        'E' => 55,
        'F' => 45,
    ];

    private array $schema;

    public function __construct()
    {
        $this->schema = $this->loadSchema();
    }

    public function transform(Student $student): array
    {
        $features = [];

        foreach ($this->schema['features'] as $definition) {
            $name = $definition['name'];
            $features[$name] = $this->mapFeature($student, $definition);
        }

        return $features;
    }

    private function loadSchema(): array
    {
        $candidatePaths = [
            storage_path('app/ml/feature_schema.json'),
            base_path('python_scripts/ml_model/models/feature_schema.json'),
            base_path('python_scripts/ml_model/feature_schema.json'),
        ];

        foreach ($candidatePaths as $path) {
            if (! is_file($path) || ! is_readable($path)) {
                continue;
            }

            $contents = file_get_contents($path);
            if ($contents === false) {
                continue;
            }

            $schema = json_decode($contents, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($schema['features'])) {
                return $schema;
            }
        }

        throw new RuntimeException('Prediction feature schema could not be loaded');
    }

    private function mapFeature(Student $student, array $definition): mixed
    {
        $name = $definition['name'];
        $default = $definition['default'] ?? null;

        return match ($name) {
            'age' => (int) ($student->age ?? $default),
            'gender' => $this->normalizeString($student->gender ?? $default),
            'gpa' => $this->determineGpa($student, $default),
            'attendance_rate' => $this->numericValue($student->attendance_rate, $default, 0, 100),
            'previous_failures' => $this->numericValue($student->previous_failures, $default, 0, 30),
            'study_hours_per_week' => $this->numericValue($student->study_hours_per_week, $default, 0, 80),
            'internet_access' => $this->booleanValue($student->internet_access, $default),
            'extracurricular_involvement' => $this->booleanValue($student->extracurricular_involvement, $default),
            'part_time_job' => $this->booleanValue($student->part_time_job, $default),
            'financial_aid' => $this->booleanValue($student->financial_aid, $default),
            'family_income' => $this->numericValue($student->family_income, $default, 0),
            'parental_education_level' => $this->normalizeString($student->parental_education_level ?? $default),
            'course_of_study' => $this->normalizeCourse($student->course_of_study ?? $default),
            'semester' => $this->numericValue($student->semester, $default, 1, 12),
            'living_situation' => $this->normalizeString($student->living_situation ?? $default),
            'distance_from_home' => $this->numericValue($student->distance_from_home, $default, 0, 2000),
            'mental_health_score' => $this->numericValue($student->mental_health_score, $default, 0, 10),
            'mode_of_transport' => $this->normalizeString($student->mode_of_transport ?? $default),
            'grades_average' => $this->calculateGradeAverage($student, $default),
            'grades_count' => $this->calculateGradeCount($student, $default),
            default => $default,
        };
    }

    private function normalizeString(?string $value): string
    {
        return Str::of((string) $value)->lower()->snake()->value();
    }

    private function normalizeCourse(?string $value): string
    {
        if (! $value) {
            return 'course_unknown';
        }

        return 'course_' . Str::of($value)->lower()->snake()->replace('__', '_')->value();
    }

    private function numericValue(mixed $value, mixed $default, ?float $min = null, ?float $max = null): float
    {
        if (! is_numeric($value)) {
            $value = $default ?? 0;
        }

        $numeric = (float) $value;

        if ($min !== null) {
            $numeric = max($min, $numeric);
        }

        if ($max !== null) {
            $numeric = min($max, $numeric);
        }

        return round($numeric, 4);
    }

    private function determineGpa(Student $student, mixed $default): float
    {
        if ($student->gpa !== null) {
            return $this->numericValue($student->gpa, $default, 0, 4);
        }

        [$points] = $this->extractGradeMetrics($student);

        if ($points === null) {
            return $this->numericValue($default, 2.5, 0, 4);
        }

        return $this->numericValue($points, 2.5, 0, 4);
    }

    private function calculateGradeAverage(Student $student, mixed $default): float
    {
        [, $percentages] = $this->extractGradeMetrics($student);

        if ($percentages === null) {
            return $this->numericValue($default, 75, 0, 100);
        }

        return $this->numericValue($percentages, 75, 0, 100);
    }

    private function calculateGradeCount(Student $student, mixed $default): float
    {
        $grades = $student->grades ?? [];

        if (! is_array($grades)) {
            return (float) ($default ?? 0);
        }

        $count = count(array_filter($grades, fn($item) => Arr::get($item, 'grade')));

        if ($count === 0 && $default !== null) {
            return (float) $default;
        }

        return (float) $count;
    }

    private function booleanValue(mixed $value, mixed $default): bool
    {
        if ($value === null) {
            return (bool) $default;
        }

        return (bool) filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    private function extractGradeMetrics(Student $student): array
    {
        $grades = $student->grades ?? [];

        if (! is_array($grades) || empty($grades)) {
            return [null, null];
        }

        $points = [];
        $percentages = [];

        foreach ($grades as $gradeEntry) {
            $gradeValue = Arr::get($gradeEntry, 'grade');

            if ($gradeValue === null) {
                continue;
            }

            if (is_numeric($gradeValue)) {
                $numericGrade = (float) $gradeValue;
                $percentages[] = $numericGrade;
                $points[] = $numericGrade / 25; // approximate conversion to 0-4 scale
                continue;
            }

            $normalized = Str::of($gradeValue)->upper()->trim()->value();

            if (isset(self::LETTER_GRADE_POINTS[$normalized])) {
                $points[] = self::LETTER_GRADE_POINTS[$normalized];
            }

            if (isset(self::LETTER_GRADE_PERCENTAGES[$normalized])) {
                $percentages[] = self::LETTER_GRADE_PERCENTAGES[$normalized];
            }
        }

        $averagePoints = ! empty($points) ? array_sum($points) / count($points) : null;
        $averagePercentages = ! empty($percentages) ? array_sum($percentages) / count($percentages) : null;

        return [$averagePoints, $averagePercentages];
    }
}
