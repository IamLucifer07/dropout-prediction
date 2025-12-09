<?php

namespace App\Http\Controllers;

use App\Models\ExternalDataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalDataController extends Controller
{
    public function index()
    {
        $datasets = ExternalDataset::where('is_active', true)
            ->orderBy('last_updated', 'desc')
            ->get();

        return response()->json($datasets);
    }

    public function syncData(Request $request)
    {
        $request->validate([
            'dataset_id' => 'required|exists:external_datasets,id'
        ]);

        $dataset = ExternalDataset::find($request->dataset_id);

        try {
            $response = Http::timeout(60)->get($dataset->source_url);

            if ($response->successful()) {
                $data = $response->json();

                // Process and store the data based on dataset type
                $this->processExternalData($dataset, $data);

                $dataset->update(['last_updated' => now()]);

                return response()->json([
                    'message' => 'Data synchronized successfully',
                    'dataset' => $dataset
                ]);
            }
        } catch (\Exception $e) {
            Log::error('External data sync error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to sync data'
            ], 500);
        }
    }

    private function processExternalData(ExternalDataset $dataset, array $data)
    {
        // This would process different types of external data
        // For now, just update metadata
        $dataset->update([
            'metadata' => array_merge($dataset->metadata ?? [], [
                'last_sync' => now(),
                'records_count' => count($data),
                'sample_data' => array_slice($data, 0, 5)
            ])
        ]);
    }
}
