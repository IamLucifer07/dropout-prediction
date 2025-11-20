<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'college_admin_id',
        'prediction_result',
        'confidence_score',
        'feature_importance',
        'model_metadata',
        'model_version',
        'input_data',
        'predicted_at',
    ];

    protected $casts = [
        'feature_importance' => 'array',
        'model_metadata' => 'array',
        'input_data' => 'array',
        'confidence_score' => 'decimal:4',
        'predicted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function collegeAdmin()
    {
        return $this->belongsTo(CollegeAdmin::class);
    }

    public function getRiskColorAttribute()
    {
        return match ($this->prediction_result) {
            'dropout' => 'red',
            'at_risk' => 'orange',
            'safe' => 'green',
            default => 'gray'
        };
    }

    public function getConfidencePercentageAttribute()
    {
        return round($this->confidence_score * 100, 2);
    }
}
