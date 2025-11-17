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
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                'prediction_result',
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month', 'prediction_result')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($group) {
                return $group->pluck('count', 'prediction_result')->toArray();
            });

        return response()->json([
            'total_students' => $totalStudents,
            'recent_predictions' => $recentPredictions,
            'risk_distribution' => $riskDistribution,
            'monthly_trends' => $monthlyTrends,
        ]);
    }
}
