<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'subject',
        'insight_type',
        'severity',
        'scan_performed_on',
        'status',
        'scan_source',
        'metadata',
    ];

    protected $casts = [
        'scan_performed_on' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }

    public function restore(): bool
    {
        return $this->update(['status' => 'active']);
    }
}
