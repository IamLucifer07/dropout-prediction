<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalDataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'source_url',
        'data_type',
        'metadata',
        'last_updated',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_updated' => 'datetime',
        'is_active' => 'boolean',
    ];
}
