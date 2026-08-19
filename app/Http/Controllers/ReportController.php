<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\KpiReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $staffId = $request->input('staff_id');
        $period = $request->input('period', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $reportType = $request->input('report_type', 'summary');

        $service = new KpiReportService($staffId, $period, $fromDate, $toDate);

        $staffList = $service->getStaffList();
        $staff = $service->getStaff();
        $periodDisplay = $service->periodDisplay();

        $report = null;
        if ($staffId && $staff) {
            $report = [
                'staff'         => $staff,
                'period_display' => $periodDisplay,
                'period_label'  => $service->periodLabel(),
                'period'        => $period,
                'from_date'     => $fromDate,
                'to_date'       => $toDate,
                'report_type'   => $reportType,
                'kpi'           => $service->calculateKpi(),
                'categories'    => $service->getCategoryBreakdown(),
                'tickets'       => $reportType === 'detailed' ? $service->getDetailedTickets() : null,
            ];
        }

        return view('reports.index', compact('report', 'staffList', 'staff', 'periodDisplay', 'period', 'fromDate', 'toDate', 'reportType', 'staffId'));
    }

    public function exportExcel(Request $request)
    {
        $staffId = $request->input('staff_id');
        $period = $request->input('period', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $reportType = $request->input('report_type', 'summary');

        $service = new KpiReportService($staffId, $period, $fromDate, $toDate);

        $staff = $service->getStaff();
        $periodDisplay = $service->periodDisplay();
        $kpi = $service->calculateKpi();
        $categories = $service->getCategoryBreakdown();
        $tickets = $reportType === 'detailed' ? $service->getDetailedTickets() : null;

        $filename = 'kpi_report_' . ($staff?->name ?? 'unknown') . '_' . now()->format('Ymd_His') . '.xls';

        $html = view('reports.export-excel', compact('staff', 'periodDisplay', 'kpi', 'categories', 'tickets', 'reportType'))
            ->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function exportPdf(Request $request): StreamedResponse
    {
        $staffId = $request->input('staff_id');
        $period = $request->input('period', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $reportType = $request->input('report_type', 'summary');

        $service = new KpiReportService($staffId, $period, $fromDate, $toDate);

        $staff = $service->getStaff();
        $periodDisplay = $service->periodDisplay();
        $kpi = $service->calculateKpi();
        $categories = $service->getCategoryBreakdown();
        $tickets = $reportType === 'detailed' ? $service->getDetailedTickets() : null;

        $html = view('reports.print', compact('staff', 'periodDisplay', 'kpi', 'categories', 'tickets', 'reportType'))
            ->render();

        $response = new StreamedResponse(function () use ($html) {
            echo $html;
        });

        $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        $response->headers->set('Content-Disposition', 'inline; filename="kpi_report.pdf"');

        return $response;
    }
}
