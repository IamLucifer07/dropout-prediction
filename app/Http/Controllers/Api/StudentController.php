<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StudentController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $query = Student::with(['latestPrediction'])
            ->where('college_admin_id', $request->user()->id);

        if ($request->has('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('risk_level')) {
            $query->whereHas('latestPrediction', function ($q) use ($request) {
                $q->where('prediction_result', $request->risk_level);
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'age' => 'required|integer|min:16|max:65',
            'gender' => 'required|in:male,female,other',
            'gpa' => 'nullable|numeric|min:0|max:4',
            'attendance_rate' => 'required|numeric|min:0|max:100',
            'grades' => 'nullable|array',
            'parental_education_level' => 'nullable|string',
            'family_income' => 'nullable|numeric|min:0',
            'mode_of_transport' => 'nullable|string',
            'internet_access' => 'boolean',
            'previous_failures' => 'integer|min:0',
            'extracurricular_involvement' => 'boolean',
            'mental_health_score' => 'nullable|numeric|min:0|max:10',
            'study_hours_per_week' => 'integer|min:0',
            'part_time_job' => 'boolean',
            'living_situation' => 'nullable|string',
            'distance_from_home' => 'integer|min:0',
            'financial_aid' => 'boolean',
            'course_of_study' => 'nullable|string',
            'semester' => 'integer|min:1',
            'additional_factors' => 'nullable|array',
        ]);

        $validated['college_admin_id'] = $request->user()->id;

        $student = Student::create($validated);

        // Make prediction
        $prediction = $this->makePrediction($student);

        return response()->json([
            'student' => $student->load('latestPrediction'),
            'prediction' => $prediction,
        ], 201);
    }

    public function show(Student $student, Request $request)
    {
        $this->authorize('view', $student);

        return response()->json($student->load(['predictions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:16|max:65',
            'gender' => 'sometimes|required|in:male,female,other',
            'gpa' => 'sometimes|nullable|numeric|min:0|max:4',
            'attendance_rate' => 'sometimes|required|numeric|min:0|max:100',
            'grades' => 'sometimes|nullable|array',
            'parental_education_level' => 'sometimes|nullable|string',
            'family_income' => 'sometimes|nullable|numeric|min:0',
            'mode_of_transport' => 'sometimes|nullable|string',
            'internet_access' => 'sometimes|boolean',
            'previous_failures' => 'sometimes|integer|min:0',
            'extracurricular_involvement' => 'sometimes|boolean',
            'mental_health_score' => 'sometimes|nullable|numeric|min:0|max:10',
            'study_hours_per_week' => 'sometimes|integer|min:0',
            'part_time_job' => 'sometimes|boolean',
            'living_situation' => 'sometimes|nullable|string',
            'distance_from_home' => 'sometimes|integer|min:0',
            'financial_aid' => 'sometimes|boolean',
            'course_of_study' => 'sometimes|nullable|string',
            'semester' => 'sometimes|integer|min:1',
            'additional_factors' => 'sometimes|nullable|array',
        ]);

        $student->update($validated);

        // Make new prediction if significant data changed
        if ($this->shouldMakeNewPrediction($request->all())) {
            $this->makePrediction($student);
        }

        return response()->json($student->load('latestPrediction'));
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    private function makePrediction(Student $student)
    {
        try {
            $inputData = $this->prepareInputData($student);

            $response = Http::timeout(30)->post(config('app.ml_api_url'), [
                'data' => $inputData,
                'model_version' => '1.0'
            ]);

            if ($response->successful()) {
                $predictionData = $response->json();

                $prediction = Prediction::create([
                    'student_id' => $student->id,
                    'college_admin_id' => $student->college_admin_id,
                    'prediction_result' => $predictionData['prediction'],
                    'confidence_score' => $predictionData['confidence'],
                    'feature_importance' => $predictionData['feature_importance'] ?? null,
                    'model_metadata' => $predictionData['model_metadata'] ?? null,
                    'model_version' => $predictionData['model_version'] ?? '1.0',
                    'input_data' => $inputData,
                    'predicted_at' => now(),
                ]);

                return $prediction;
            }
        } catch (\Exception $e) {
            Log::error('Prediction API error: ' . $e->getMessage());

            // Fallback prediction based on simple rules
            return $this->makeFallbackPrediction($student);
        }

        return null;
    }

    private function prepareInputData(Student $student)
    {
        return [
            'age' => $student->age,
            'gender' => $student->gender,
            'gpa' => $student->gpa ?? 0,
            'attendance_rate' => $student->attendance_rate,
            'parental_education_level' => $student->parental_education_level,
            'family_income' => $student->family_income ?? 0,
            'internet_access' => $student->internet_access,
            'previous_failures' => $student->previous_failures,
            'extracurricular_involvement' => $student->extracurricular_involvement,
            'mental_health_score' => $student->mental_health_score ?? 5,
            'study_hours_per_week' => $student->study_hours_per_week,
            'part_time_job' => $student->part_time_job,
            'distance_from_home' => $student->distance_from_home,
            'financial_aid' => $student->financial_aid,
            'semester' => $student->semester,
        ];
    }

    private function makeFallbackPrediction(Student $student)
    {
        // Simple rule-based fallback
        $riskScore = 0;

        if ($student->attendance_rate < 70) $riskScore += 3;
        if ($student->gpa < 2.0) $riskScore += 3;
        if ($student->previous_failures > 2) $riskScore += 2;
        if ($student->mental_health_score < 4) $riskScore += 2;
        if (!$student->internet_access) $riskScore += 1;
        if ($student->study_hours_per_week < 10) $riskScore += 1;

        $prediction = $riskScore >= 6 ? 'dropout' : ($riskScore >= 3 ? 'at_risk' : 'safe');
        $confidence = max(0.5, min(0.95, 0.6 + ($riskScore * 0.05)));

        return Prediction::create([
            'student_id' => $student->id,
            'college_admin_id' => $student->college_admin_id,
            'prediction_result' => $prediction,
            'confidence_score' => $confidence,
            'feature_importance' => null,
            'model_metadata' => ['fallback' => true],
            'model_version' => 'fallback',
            'input_data' => $this->prepareInputData($student),
            'predicted_at' => now(),
        ]);
    }

    private function shouldMakeNewPrediction(array $data)
    {
        $significantFields = [
            'gpa',
            'attendance_rate',
            'previous_failures',
            'mental_health_score',
            'study_hours_per_week'
        ];

        return collect($significantFields)->some(fn($field) => isset($data[$field]));
    }
}
