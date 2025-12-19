<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\StudentFeatureTransformer;
use Inertia\Inertia;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly StudentFeatureTransformer $featureTransformer) {}

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

        $validated['grades'] = $validated['grades'] ?? [];

        // Get authenticated user (college admin)
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated['college_admin_id'] = $user->id;

        $student = Student::create($validated);

        // Don't automatically make prediction - let admin choose when to predict
        return response()->json([
            'student' => $student->load('latestPrediction'),
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

        // Always make a new prediction after update, using the same process as new student
        // Use the model from the latest prediction if available, otherwise use default
        $student->load('latestPrediction');
        $latestPrediction = $student->latestPrediction;
        $modelToUse = 'random_forest.joblib'; // Default model

        // Valid model names that exist in the models directory
        $validModels = ['random_forest.joblib', 'decision_tree.joblib'];

        if ($latestPrediction) {
            // Try to get the model from metadata
            if (isset($latestPrediction->model_metadata['attempted_model'])) {
                $attemptedModel = $latestPrediction->model_metadata['attempted_model'];
                $modelWithExtension = substr($attemptedModel, -7) === '.joblib'
                    ? $attemptedModel
                    : $attemptedModel . '.joblib';

                // Only use if it's a valid model
                if (in_array($modelWithExtension, $validModels)) {
                    $modelToUse = $modelWithExtension;
                }
            } elseif ($latestPrediction->model_version) {
                // Extract model name from model_version (remove "(fallback)" if present)
                $modelVersion = str_replace(' (fallback)', '', $latestPrediction->model_version);

                // Skip invalid model versions (seeded data, fallback, unknown, etc.)
                $invalidVersions = ['fallback', 'unknown', 'seeded_data_v1.0', 'seeded_data'];

                if (!in_array($modelVersion, $invalidVersions) && strpos($modelVersion, 'seeded') === false) {
                    $modelWithExtension = substr($modelVersion, -7) === '.joblib'
                        ? $modelVersion
                        : $modelVersion . '.joblib';

                    // Only use if it's a valid model
                    if (in_array($modelWithExtension, $validModels)) {
                        $modelToUse = $modelWithExtension;
                    }
                }
            }
        }

        // Make prediction with the determined model (same process as new student)
        $this->processPrediction($student, $modelToUse);

        // Reload student with latest prediction
        $student->refresh();
        return response()->json($student->load('latestPrediction'));
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    public function makePrediction(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $request->validate([
            'model' => 'nullable|string',
        ]);

        $prediction = $this->processPrediction($student, $request->input('model'));

        if (! $prediction) {
            return response()->json(['error' => 'Unable to generate prediction'], 500);
        }

        return response()->json($prediction);
    }

    public function getAvailableModels()
    {
        try {
            $response = Http::timeout(10)->get(str_replace('/predict', '/models', config('services.ml.url')));

            if (! $response->successful()) {
                Log::warning('Models API returned non-success status', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // Return default models if API fails
                return response()->json([
                    'models' => [
                        ['name' => 'random_forest.joblib'],
                        ['name' => 'decision_tree.joblib'],
                    ]
                ]);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('Models API error: ' . $e->getMessage());

            // Return default models on error
            return response()->json([
                'models' => [
                    ['name' => 'random_forest.joblib'],
                    ['name' => 'decision_tree.joblib'],
                ]
            ]);
        }
    }

    private function processPrediction(Student $student, ?string $model = null)
    {
        $modelName = $model ?? 'random_forest.joblib';
        $cleanModelName = str_replace('.joblib', '', $modelName);

        try {
            $inputData = $this->featureTransformer->transform($student);
            $mlUrl = config('services.ml.url');

            Log::info('Making prediction request', [
                'student_id' => $student->id,
                'model' => $modelName,
                'ml_url' => $mlUrl,
            ]);

            $response = Http::timeout(30)->post($mlUrl, [
                'data' => $inputData,
                'model' => $modelName,
            ]);

            if (! $response->successful()) {
                Log::warning('Prediction API returned non-success status', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'model' => $modelName,
                    'ml_url' => $mlUrl,
                ]);

                return $this->makeFallbackPrediction($student, $cleanModelName);
            }

            $predictionData = $response->json();

            // Extract model name from metadata or use the provided model parameter
            $returnedModelName = $predictionData['model_metadata']['model_path'] ?? $modelName ?? 'unknown';
            // Remove .joblib extension if present for cleaner display
            $finalModelName = str_replace('.joblib', '', $returnedModelName);

            $prediction = Prediction::create([
                'student_id' => $student->id,
                'college_admin_id' => $student->college_admin_id,
                'prediction_result' => $predictionData['prediction'],
                'confidence_score' => $predictionData['confidence'] ?? 0,
                'feature_importance' => $predictionData['feature_importance'] ?? null,
                'model_metadata' => array_merge(
                    $predictionData['model_metadata'] ?? [],
                    ['probabilities' => $predictionData['probabilities'] ?? []]
                ),
                'model_version' => $finalModelName,
                'input_data' => $inputData,
                'predicted_at' => now(),
            ]);

            Log::info('Prediction created successfully', [
                'student_id' => $student->id,
                'model_version' => $finalModelName,
                'prediction_result' => $predictionData['prediction'],
            ]);

            return $prediction;
        } catch (\Exception $e) {
            Log::error('Prediction API error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'model' => $modelName,
                'ml_url' => config('services.ml.url'),
            ]);

            // Fallback prediction based on simple rules, but still use the model name
            return $this->makeFallbackPrediction($student, $cleanModelName);
        }
    }

    private function makeFallbackPrediction(Student $student, ?string $modelName = null)
    {
        $features = $this->featureTransformer->transform($student);
        $riskScore = 0;

        if (($features['attendance_rate'] ?? 0) < 70) {
            $riskScore += 3;
        }

        if (($features['gpa'] ?? 0) < 2.0) {
            $riskScore += 3;
        }

        if (($features['previous_failures'] ?? 0) > 2) {
            $riskScore += 2;
        }

        if (($features['mental_health_score'] ?? 5) < 4) {
            $riskScore += 2;
        }

        if (empty($features['internet_access'])) {
            $riskScore += 1;
        }

        if (($features['study_hours_per_week'] ?? 0) < 10) {
            $riskScore += 1;
        }

        $prediction = $riskScore >= 6 ? 'dropout' : ($riskScore >= 3 ? 'at_risk' : 'safe');
        $confidence = max(0.5, min(0.95, 0.55 + ($riskScore * 0.05)));

        // Use the model name if provided, otherwise mark as fallback
        $finalModelName = $modelName ? ($modelName . ' (fallback)') : 'fallback';

        return Prediction::create([
            'student_id' => $student->id,
            'college_admin_id' => $student->college_admin_id,
            'prediction_result' => $prediction,
            'confidence_score' => $confidence,
            'feature_importance' => null,
            'model_metadata' => ['fallback' => true, 'attempted_model' => $modelName],
            'model_version' => $finalModelName,
            'input_data' => $features,
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

    public function showStudentListPage(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $students = Student::query()
            ->where('college_admin_id', $request->user()->id)
            ->with('latestPrediction')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return Inertia::render('students/StudentsList', [
            'students' => $students,
        ]);
    }

    public function showStudentDetailPage(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['predictions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('students/StudentDetail', [
            'student' => $student,
        ]);
    }

    public function showStudentEditPage(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $student->load(['predictions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('students/StudentForm', [
            'student' => $student,
        ]);
    }
}
