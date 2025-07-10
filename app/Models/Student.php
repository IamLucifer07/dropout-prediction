<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_admin_id',
        'full_name',
        'age',
        'gender',
        'gpa',
        'attendance_rate',
        'grades',
        'parental_education_level',
        'family_income',
        'mode_of_transport',
        'internet_access',
        'previous_failures',
        'extracurricular_involvement',
        'mental_health_score',
        'study_hours_per_week',
        'part_time_job',
        'living_situation',
        'distance_from_home',
        'financial_aid',
        'course_of_study',
        'semester',
        'additional_factors',
    ];

    protected $casts = [
        'grades' => 'array',
        'internet_access' => 'boolean',
        'extracurricular_involvement' => 'boolean',
        'part_time_job' => 'boolean',
        'financial_aid' => 'boolean',
        'additional_factors' => 'array',
        'attendance_rate' => 'decimal:2',
        'gpa' => 'decimal:2',
        'family_income' => 'decimal:2',
        'mental_health_score' => 'decimal:2',
    ];

    public function collegeAdmin()
    {
        return $this->belongsTo(CollegeAdmin::class);
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }

    public function latestPrediction()
    {
        return $this->hasOne(Prediction::class)->latestOfMany();
    }

    public function getDropoutRiskLevelAttribute()
    {
        $prediction = $this->latestPrediction;
        return $prediction ? $prediction->prediction_result : 'unknown';
    }
}
