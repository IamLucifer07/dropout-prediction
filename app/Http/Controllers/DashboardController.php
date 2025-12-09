<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $adminId = $request->user()->id;

        $totalStudents = Student::where('college_admin_id', $adminId)->count();

        $recentPredictions = Prediction::where('college_admin_id', $adminId)
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $riskDistribution = Prediction::where('college_admin_id', $adminId)
            ->select('prediction_result', DB::raw('count(*) as count'))
            ->groupBy('prediction_result')
            ->get()
            ->pluck('count', 'prediction_result')
            ->toArray();

        $monthlyTrends = Prediction::where('college_admin_id', $adminId)
            ->where('created_at', '>=', now()->subMonths(12))
            ->orderBy('created_at')
            ->get() // First, get all the relevant records from the database
            ->groupBy(function ($prediction) {
                // Group them by month using PHP's Carbon library (which is built-in)
                return $prediction->created_at->format('Y-m');
            })
            ->map(function ($monthlyPredictions) {
                // For each month, count the occurrences of each prediction result
                return $monthlyPredictions->countBy('prediction_result');
            });

        return response()->json([
            'total_students' => $totalStudents,
            'recent_predictions' => $recentPredictions,
            'risk_distribution' => $riskDistribution,
            'monthly_trends' => $monthlyTrends,
        ]);
    }
}
