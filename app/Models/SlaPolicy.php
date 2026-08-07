<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    protected $fillable = [
        'name',
        'priority',
        'response_hours',
        'resolution_hours',
        'escalation_enabled',
        'is_active',
    ];

    protected $casts = [
        'escalation_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}