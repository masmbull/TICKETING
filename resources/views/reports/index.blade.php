@extends('layouts.app')

@section('title', 'Reports - IT Support KPI Report - MITO IT Helpdesk')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Reports</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">IT Support KPI Report</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Filters</h2>
        </div>
        <div class="p-4">
            <form method="GET" id="reportForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Staff</label>
                    <select name="staff_id" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Select Staff</option>
                        <option value="0" {{ request('staff_id') == '0' ? 'selected' : '' }}>All IT Personnel</option>
                        @foreach($staffList as $s)
                        <option value="{{ $s->id }}" {{ (request('staff_id') == $s->id) || ($report && $report['staff']?->id == $s->id) ? 'selected' : '' }}>{{ $s->name }} ({{ ucfirst($s->role?->slug) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Period</label>
                    <select name="period" id="periodSelect" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                        <option value="this_month" {{ ($period ?? 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ ($period ?? 'this_month') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ ($period ?? 'this_month') === 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ ($period ?? 'this_month') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div x-data="{ show: false }" x-init="show = document.getElementById('periodSelect').value === 'custom'; document.getElementById('periodSelect').addEventListener('change', e => show = e.target.value === 'custom')" x-show="show">
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                </div>

                <div x-data="{ show: false }" x-init="show = document.getElementById('periodSelect').value === 'custom'; document.getElementById('periodSelect').addEventListener('change', e => show = e.target.value === 'custom')" x-show="show">
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                           class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Report Type</label>
                    <select name="report_type" class="w-full px-3 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-lg text-slate-900 dark:text-white">
                        <option value="summary" {{ ($reportType ?? 'summary') === 'summary' ? 'selected' : '' }}>Summary</option>
                        <option value="detailed" {{ ($reportType ?? 'summary') === 'detailed' ? 'selected' : '' }}>Detailed</option>
                    </select>
                </div>
            </form>

            <div class="flex justify-end gap-2 mt-4" x-data="{ show: false }" x-init="show = document.getElementById('periodSelect').value === 'custom'; document.getElementById('periodSelect').addEventListener('change', e => show = e.target.value === 'custom')">
                <template x-if="!show">
                    <span class="text-xs text-slate-400 dark:text-slate-500 self-center">Custom range fields appear when you select "Custom Range" above.</span>
                </template>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('reportForm').submit()"
                        class="px-4 py-2 bg-[#E30613] hover:bg-[#c4050f] text-white text-sm font-medium rounded-lg transition-colors">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    {{-- Report Output --}}
    @if($report)
    <div class="space-y-4">
        {{-- Report Header --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">IT SUPPORT KPI REPORT</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Staff: <span class="font-medium text-slate-700 dark:text-slate-200">{{ $report['staff']?->name ?? 'All IT Personnel' }}</span>
                        &nbsp;|&nbsp; Period: <span class="font-medium">{{ $report['period_display'] }}</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('reports.export.excel', request()->query()) }}"
                       class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                        Export Excel
                    </a>
                    <a href="{{ route('reports.export.pdf', request()->query()) }}" target="_blank"
                       class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Export PDF
                    </a>
                </div>
            </div>

            {{-- KPI Metrics --}}
            <div class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $report['kpi']['total'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Tickets Handled</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $report['kpi']['completed'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Completed</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $report['kpi']['in_progress'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total In Progress</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $report['kpi']['waiting_confirmation'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Waiting Confirmation</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $report['kpi']['completion_rate'] }}%</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Completion Rate</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $report['kpi']['avg_resolution'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Avg Resolution Time</p>
                    </div>
                </div>

                {{-- SLA --}}
                <div class="mb-4">
                    <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">SLA Performance</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-emerald-500/10 dark:bg-emerald-500/5 border border-emerald-200 dark:border-emerald-900/30 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $report['kpi']['sla']['Excellent'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Excellent</p>
                        </div>
                        <div class="bg-blue-500/10 dark:bg-blue-500/5 border border-blue-200 dark:border-blue-900/30 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $report['kpi']['sla']['Normal'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Normal</p>
                        </div>
                        <div class="bg-red-500/10 dark:bg-red-500/5 border border-red-200 dark:border-red-900/30 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $report['kpi']['sla']['Poor'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Poor</p>
                        </div>
                    </div>
                </div>

                {{-- Category Breakdown --}}
                <div>
                    <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Category Breakdown</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Category</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Tickets</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Completed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @forelse($report['categories'] as $cat)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="px-4 py-3 text-slate-900 dark:text-white font-medium">{{ $cat->category }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300 text-right">{{ $cat->tickets }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300 text-right">{{ $cat->completed }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">No category data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Ticket Table --}}
        @if($report['report_type'] === 'detailed')
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Ticket Details</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-700/50">
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Ticket ID</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Category</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Subcategory</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Created</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Assigned</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Completed</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">SLA Priority</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">SLA Result</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Resolution</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($report['tickets'] as $ticket)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="text-[#E30613] hover:text-[#c4050f] font-medium">{{ $ticket->ticket_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $ticket->category->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $ticket->subCategory->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $ticket->created_at?->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $ticket->assigned_at?->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $ticket->completed_at?->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($ticket->sla_priority)
                                <span class="px-2 py-0.5 rounded text-xs font-medium border
                                    @if($ticket->sla_priority === 'critical') bg-red-500/10 text-red-600 border-red-200
                                    @elseif($ticket->sla_priority === 'high') bg-orange-500/10 text-orange-600 border-orange-200
                                    @elseif($ticket->sla_priority === 'medium') bg-blue-500/10 text-blue-600 border-blue-200
                                    @else bg-slate-100 text-slate-600 border-slate-200 @endif">
                                    {{ ucfirst($ticket->sla_priority) }}
                                </span>
                                @else
                                <span class="text-slate-400">No SLA</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($ticket->sla_status === 'Excellent')
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-600 border border-emerald-200">Excellent</span>
                                @elseif($ticket->sla_status === 'Normal')
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-600 border border-blue-200">Normal</span>
                                @elseif($ticket->sla_status === 'Poor')
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-500/10 text-red-600 border border-red-200">Poor</span>
                                @elseif($ticket->sla_status === 'No SLA')
                                <span class="text-slate-400">No SLA</span>
                                @else
                                <span class="text-slate-400">Not Evaluated</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300 max-w-xs truncate">{{ $ticket->resolution ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="px-4 py-8 text-center text-sm text-slate-400">No tickets found for this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    @elseif(!empty($staffId) && !$staff)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="p-8 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">Invalid staff selection. Please select a valid staff member.</p>
        </div>
    </div>
    @endif
</div>
@endsection
