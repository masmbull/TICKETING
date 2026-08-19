<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;

class KpiReportService
{
    public function __construct(
        protected ?int $staffId,
        protected string $period,
        protected ?string $fromDate = null,
        protected ?string $toDate = null,
    ) {}

    public function periodRange(): array
    {
        switch ($this->period) {
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $start = $this->fromDate ? Carbon::parse($this->fromDate)->startOfDay() : Carbon::now()->startOfMonth();
                $end = $this->toDate ? Carbon::parse($this->toDate)->endOfDay() : Carbon::now()->endOfMonth();
                if ($end < $start) {
                    [$start, $end] = [$end, $start];
                }
                break;
            case 'this_month':
            default:
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
        }

        return [$start, $end];
    }

    public function periodLabel(): string
    {
        [$start, $end] = $this->periodRange();

        if ($this->period === 'custom') {
            return $start->format('d M Y') . ' – ' . $end->format('d M Y');
        }

        return match ($this->period) {
            'last_month' => 'Last Month',
            'this_year'  => 'This Year',
            'custom'     => 'Custom Range',
            default      => 'This Month',
        };
    }

    public function periodDisplay(): string
    {
        [$start, $end] = $this->periodRange();
        return $start->format('d M Y') . ' – ' . $end->format('d M Y');
    }

    public function getTickets()
    {
        [$start, $end] = $this->periodRange();

        $query = Ticket::where('assignee_id', $this->staffId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('assigned_at', [$start, $end])
                  ->orWhere(function ($qq) use ($start, $end) {
                      $qq->whereNull('assigned_at')
                         ->whereBetween('created_at', [$start, $end]);
                  });
            })
            ->with(['category', 'subCategory', 'assignee', 'completedBy']);

        return $query->get();
    }

    public function getDetailedTickets()
    {
        return $this->getTickets()->sortByDesc('created_at');
    }

    public function calculateKpi(): array
    {
        $tickets = $this->getTickets();

        $total = $tickets->count();
        $completed = $tickets->where('status', 'Completed')->count();
        $inProgress = $tickets->where('status', 'In Progress')->count();
        $waitingConfirmation = $tickets->where('status', 'Waiting Confirmation')->count();

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        $resolutionTimes = $tickets->filter(fn ($t) => $t->status === 'Completed' && $t->completed_at)
            ->map(function ($t) {
                $start = $t->assigned_at ?? $t->created_at;
                return $start ? $t->completed_at->diffInMinutes($start) : null;
            })
            ->filter()
            ->values();

        $avgResolutionMinutes = $resolutionTimes->avg() ?? 0;

        $slaCounts = ['Excellent' => 0, 'Normal' => 0, 'Poor' => 0];
        foreach ($tickets as $ticket) {
            $status = $ticket->sla_status;
            if (in_array($status, ['Excellent', 'Normal', 'Poor'])) {
                $slaCounts[$status]++;
            }
        }

        return [
            'total'              => $total,
            'completed'          => $completed,
            'in_progress'        => $inProgress,
            'waiting_confirmation' => $waitingConfirmation,
            'completion_rate'    => $completionRate,
            'avg_resolution'     => $this->formatDuration($avgResolutionMinutes),
            'avg_resolution_minutes' => $avgResolutionMinutes,
            'sla' => $slaCounts,
        ];
    }

    public function getCategoryBreakdown(): \Illuminate\Support\Collection
    {
        $tickets = $this->getTickets();

        return $tickets->groupBy(fn ($t) => $t->category?->name ?? 'Uncategorized')
            ->map(fn ($group) => (object) [
                'category' => $group->first()->category?->name ?? 'Uncategorized',
                'tickets'  => $group->count(),
                'completed' => $group->where('status', 'Completed')->count(),
            ])
            ->sortByDesc('tickets')
            ->values();
    }

    public function getStaff(): ?User
    {
        if (!$this->staffId) {
            return null;
        }

        return User::find($this->staffId);
    }

    public function getStaffList(): \Illuminate\Support\Collection
    {
        return User::where('is_active', true)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'manager', 'staff']);
            })
            ->orderBy('name')
            ->get();
    }

    protected function formatDuration(float $minutes): string
    {
        if ($minutes <= 0) return '—';

        $hours = (int) floor($minutes / 60);
        $mins = (int) ($minutes % 60);

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }

        return "{$mins}m";
    }
}
