<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $country = $request->input('country');
        $subRegion = $request->input('sub_region');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = Applicant::query();

        if ($country) {
            $query->where('country', $country);
        }

        if ($subRegion) {
            $query->where('sub_region', $subRegion);
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $applicants = $query->get();

        // Line Chart: Applications Over Time
        $applicationsByDate = Applicant::query()
        ->when($country, fn($q) => $q->where('country', $country))
        ->when($subRegion, fn($q) => $q->where('sub_region', $subRegion))
        ->when($fromDate && $toDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
        ->select(DB::raw('DATE(created_at) as app_date'), DB::raw('COUNT(*) as count'))
        ->groupBy('app_date')
        ->orderBy('app_date')
        ->get();


        // Pie Chart: Country Distribution
        $applicationsByCountry = $query->select('country', DB::raw('count(*) as count'))
            ->groupBy('country')->get();

        // Bar Chart: Sub-region Distribution
        $applicationsByRegion = $query->select('sub_region', DB::raw('count(*) as count'))
            ->groupBy('sub_region')->get();

        // Histogram: Covered Countries (flattened JSON)
        $coveredCountryCounts = [];
        foreach ($applicants as $a) {
            $countries = json_decode($a->covered_countries, true) ?? [];
            foreach ($countries as $c) {
                $coveredCountryCounts[$c] = ($coveredCountryCounts[$c] ?? 0) + 1;
            }
        }

        return view('reports.index', [
            'applicants' => $applicants,
            'applicationsByDate' => $applicationsByDate,
            'applicationsByCountry' => $applicationsByCountry,
            'applicationsByRegion' => $applicationsByRegion,
            'coveredCountryCounts' => $coveredCountryCounts,
            'filters' => compact('country', 'subRegion', 'fromDate', 'toDate'),
        ]);
    }
}
