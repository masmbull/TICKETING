<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'description',
        'problem_analysis',
        'resolution',
        'status',
        'priority',
        'user_id',
        'category_id',
        'sub_category_id',
        'assignee_id',
        'department_id',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'completed_at',
        'completed_by',
        'sla_priority',
        'sla_started_at',
        'sla_deadline',
        'assigned_at',
        'problem_analysis_at',
        'resolution_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'completed_at' => 'datetime',
        'sla_started_at' => 'datetime',
        'sla_deadline' => 'datetime',
        'assigned_at' => 'datetime',
        'problem_analysis_at' => 'datetime',
        'resolution_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the category for the ticket.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the sub category for the ticket.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function getSlaStatusAttribute(): string
    {
        if (!$this->sla_deadline || !$this->sla_started_at) {
            return 'No SLA';
        }

        if ($this->status !== 'Completed') {
            return 'Not Evaluated';
        }

        $totalMinutes = $this->sla_started_at->diffInMinutes($this->sla_deadline);
        if ($totalMinutes <= 0) {
            return 'Excellent';
        }

        $elapsedAt = $this->completed_at ?? now();
        $elapsedMinutes = $this->sla_started_at->diffInMinutes($elapsedAt);
        $ratio = $elapsedMinutes / $totalMinutes;

        if ($ratio < 0.5) {
            return 'Excellent';
        }

        if ($ratio <= 1.0) {
            return 'Normal';
        }

        return 'Poor';
    }

    public function getSlaPerformanceAttribute(): ?string
    {
        if ($this->status !== 'Completed' || !$this->sla_started_at || !$this->sla_deadline || !$this->completed_at) {
            return null;
        }

        $slaMinutes = $this->sla_started_at->diffInMinutes($this->sla_deadline);

        if ($slaMinutes <= 0) {
            return null;
        }

        $actualMinutes = $this->sla_started_at->diffInMinutes($this->completed_at);
        $ratio = $actualMinutes / $slaMinutes;

        if ($ratio < 2 / 3) {
            return 'EXCELLENT';
        }

        if ($ratio <= 1.0) {
            return 'NORMAL';
        }

        return 'POOR';
    }

    public function getTimelineAttribute(): array
    {
        $events = [];

        $events[] = [
            'label' => 'Ticket Created',
            'timestamp' => $this->created_at,
            'actor' => $this->user?->name,
        ];

        if ($this->assigned_at) {
            $events[] = [
                'label' => 'Assigned',
                'timestamp' => $this->assigned_at,
                'actor' => $this->assignee?->name,
            ];
        }

        if ($this->problem_analysis_at) {
            $events[] = [
                'label' => 'Problem Analysis',
                'timestamp' => $this->problem_analysis_at,
                'actor' => $this->assignee?->name,
            ];
        }

        if ($this->status === 'In Progress' || $this->status === 'Completed') {
            $events[] = [
                'label' => 'In Progress',
                'timestamp' => $this->problem_analysis_at ?? $this->assigned_at ?? $this->created_at,
                'actor' => $this->assignee?->name,
            ];
        }

        if ($this->resolution_at) {
            $events[] = [
                'label' => 'Resolution',
                'timestamp' => $this->resolution_at,
                'actor' => $this->assignee?->name,
            ];
        }

        if ($this->completed_at) {
            $events[] = [
                'label' => 'Completed',
                'timestamp' => $this->completed_at,
                'actor' => $this->completedBy?->name,
            ];
        }

        return $events;
    }
}
