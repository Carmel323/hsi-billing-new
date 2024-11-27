<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Mail\ClickUsage;
use App\Models\BudgetCapSettings;
use App\Models\Partner;
use App\Models\Clicks;
use App\Models\Feature;
use App\Models\PartnersAffiliates;
use App\Models\Plans;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use App\Models\Subscriptions;
use Carbon\Carbon;
use Illuminate\Http\Response;
use PDF;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use SplTempFileObject;

class ClicksController extends Controller
{
    public function index()
    {
        $clicks = Clicks::orderBy('click_ts', 'desc')->simplePaginate(50);

        $partnerCounts = Clicks::select(
            'partners_affiliates.id as id',
            'partners.id as partner_id',
            'affiliates.isp_affiliate_id as affiliate_id',
            'partners.company_name',
            'affiliates.domain_name',
            DB::raw('COUNT(*) as count')
        )
            ->join('partners_affiliates', 'clicks.partners_affiliates_id', '=', 'partners_affiliates.id')
            ->join('partners', 'partners_affiliates.partner_id', '=', 'partners.id')
            ->join('affiliates', 'partners_affiliates.affiliate_id', '=', 'affiliates.id')
            ->groupBy('partners_affiliates.id', 'partners.id', 'affiliates.isp_affiliate_id', 'partners.company_name', 'affiliates.domain_name')
            ->orderBy('partners_affiliates.id')
            ->get();


        return view('admin.clicks', compact('clicks', 'partnerCounts'));
    }


    public function clicksLimitReminder(Request $request)
    {
        $payload = $request->json()->all();

        if (isset($payload['isp_affiliate_id'], $payload['usage_percent'])) {

            $ispPartnerId = $payload['isp_affiliate_id'];

            $usagePercent = $payload['usage_percent'];


            $partner = Partner::where('isp_affiliate_id', $ispPartnerId)->first();

            if ($partner) {

                $firstName = $partner->first_name;

                $email = $partner->email;

                Mail::to($email)->send(new ClickUsage($usagePercent, $firstName));

                return response()->json(['message' => 'Email sent successfully'], 200);
            } else {

                return response()->json(['error' => 'Partner not found or email mismatch'], 404);
            }
        } else {

            return response()->json(['error' => 'Missing required parameters in the payload'], 400);
        }
    }

    public function showClicksReportNewOld(Request $request)
    {
        $partnerId = Session::get('loginId');
        $partner = Partner::where('zoho_cust_id', $partnerId)->first();
        $is_daily_plan = false;
        $is_weekly_plan = false;
        $is_monthly_plan = false;

        $current_subscription = Subscriptions::where('zoho_cust_id', $partnerId)->where('status', 'live')->first();

        if ($current_subscription) {
            $current_plan = Plans::where('plan_id', $current_subscription->plan_id)->first();
            $current_plan_feature = Feature::where('plan_code', $current_plan->plan_code)->first();
            $is_monthly_plan = stripos($current_plan_feature->features_json['reporting'], 'monthly') !== false;
            $is_weekly_plan = stripos($current_plan_feature->features_json['reporting'], 'weekly') !== false;
            $is_daily_plan = stripos($current_plan_feature->features_json['reporting'], 'daily') !== false;
        }

        if (!$partner) {
            return redirect()->route('partner.reports')->withErrors('Partner not found');
        }

        $affiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

        $query = Clicks::whereIn('partners_affiliates_id', $affiliateIds);

        $now = Carbon::now();
        $filter = $request->input('filter', 'mtd');
        $dataSplit = $request->input('data_split', 'daily');
        $dateFrom = null;
        $dateTo = null;

        if ($request->has('date_from')) {
            $dateFrom = Carbon::parse($request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $dateTo = Carbon::parse($request->get('date_to'));
        }

        switch ($filter) {
            case 'mtd':
                $dateFrom =  $now->copy()->startOfMonth();
                $dateTo =  $now->copy()->subDay();
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy();
                break;
            case 'last_12_months':
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
            case 'last_6_months':
                $dateFrom = $now->copy()->subMonths(6);
                $dateTo = $now->copy();
                break;
            case 'last_3_months':
                $dateFrom = $now->copy()->subMonths(3);
                $dateTo = $now->copy();
                break;
            case 'last_1_month':
                $dateFrom =  $now->copy()->subMonth();
                $dateTo = $now->copy();
                break;
            case 'last_7_days':
                $dateFrom = $now->copy()->subDays(7);
                $dateTo = $now->copy();
                break;
            case '-':
                $dateFrom = Carbon::parse($request->get('date_from'));
                $dateTo = Carbon::parse($request->get('date_to'));
                break;
            default:
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
        }


        $query->where('click_ts', '>=', $dateFrom)
            ->where('click_ts', '<=', $dateTo);

        $total_clicks = $query->count();

        $chartData = [];

        if ($dataSplit == 'daily') {
            $results = $query->select(DB::raw('DATE(click_ts) as date, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('DATE(click_ts)'))
                ->orderBy(DB::raw('DATE(click_ts)'), 'asc')
                ->get();

            foreach ($results as $result) {
                $formattedDate = Carbon::parse($result->date)->format('d M Y');
                $chartData[] = [
                    'click_date' => $formattedDate,
                    'click_count' => $result->total_clicks,
                ];
            }
        } elseif ($dataSplit == 'weekly') {

            $results = $query->select(DB::raw('YEAR(click_ts) as year, WEEK(click_ts) as week, MIN(click_ts) as from_date, MAX(click_ts) as to_date, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'), 'asc')
                ->get();

            foreach ($results as $result) {
                $fromDate = Carbon::parse($result->from_date)->format('d M Y');
                $toDate = Carbon::parse($result->to_date)->format('d M Y');
                $chartData[] = [
                    'click_date' => 'Week ' . $result->week . ', ' . $result->year . ' (' . $fromDate . ' to ' . $toDate . ')',
                    'click_count' => $result->total_clicks,
                ];
            }
        } else {

            $results = $query->select(DB::raw('YEAR(click_ts) as year, MONTH(click_ts) as month, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'), 'asc')
                ->get();
            foreach ($results as $result) {

                $formattedDate = Carbon::createFromDate($result->year, $result->month)->format('M Y');
                $chartData[] = [
                    'click_date' => $formattedDate,
                    'click_count' => $result->total_clicks,
                ];
            }
        }

        $topN = $request->input('topN', 10);

        $topZipCodes = $this->getTopZipCodes($partner->id, $filter, $topN);

        $filterText = $this->getFilterText($filter);

        if ($request->has('download')) {
            $pdf = PDF::loadView('partner.clicks-report-pdf', compact('partner', 'chartData', 'topZipCodes', 'filterText', 'topN'));

            return $pdf->download('clicks_report.pdf');
        }

        $showModal = false;
        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
        $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();
        $dateFrom = Carbon::parse($dateFrom)->format('d/m/Y');
        $dateTo = Carbon::parse($dateTo)->format('d/m/Y');

        // if ($availability_data === null || $company_info === null) {
        //     $showModal = true;
        // }
        return view('partner.clicks-report', compact('partner', 'chartData', 'topZipCodes', 'dateFrom', 'dateTo', 'filterText', 'topN', 'filter', 'dataSplit', 'is_daily_plan', 'current_subscription', 'is_monthly_plan', 'is_weekly_plan', 'total_clicks', 'showModal', 'availability_data', 'company_info'));
    }

    public function showClicksReport(Request $request)
    {
        $zohoCustId = Session::get('loginId');
        $partner = Partner::where('zoho_cust_id', $zohoCustId)->first();
        $is_daily_plan = false;
        $is_weekly_plan = false;
        $is_monthly_plan = false;

        $current_subscription = Subscriptions::where('zoho_cust_id', $zohoCustId)->where('status', 'live')->first();

        if ($current_subscription) {
            $current_plan = Plans::where('plan_id', $current_subscription->plan_id)->first();
            $current_plan_feature = Feature::where('plan_code', $current_plan->plan_code)->first();
            $is_monthly_plan = stripos($current_plan_feature->features_json['reporting'], 'monthly') !== false;
            $is_weekly_plan = stripos($current_plan_feature->features_json['reporting'], 'weekly') !== false;
            $is_daily_plan = stripos($current_plan_feature->features_json['reporting'], 'daily') !== false;
        }

        if (!$partner) {
            return redirect()->route('partner.reports')->withErrors('Partner not found');
        }

        $partnerAffiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

        $filter = $request->input('filter', 'mtd');

        $dataSplit = $request->input('data_split', 'daily');

        $now = Carbon::now();
        if ($filter === 'custom') {
            if ($request->has('date_from')) {
                $dateFrom = Carbon::parse($request->get('date_from'));
            }
            if ($request->has('date_to')) {
                $dateTo = Carbon::parse($request->get('date_to'));
            }
        } else {

            [$dateFrom, $dateTo] = $this->getDateRange($filter, $request, $now);
        }

        $daysInRange = $dateTo->diffInDays($dateFrom) + 1;

        $query = DB::table('clicks as c')
            ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
            ->whereBetween('c.click_ts', [$dateFrom, $dateTo]);

        $chartData = $this->getChartData($query, $dataSplit);

        $subscription = Subscriptions::where('zoho_cust_id', $zohoCustId)->first();

        $totalCost = 0;

        $invoicePace = 0;

        $clicksPace = 0;



        if ($subscription) {

            $planId = $subscription->plan_id;

            $plan = Plans::where('plan_id', $planId)->first();

            if ($plan) {

                if ($plan->is_cpc) {

                    $now = Carbon::now();

                    $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');

                    $todayDate = $now->format('Y-m-d');

                    $todayDayNumber = $now->format('j');

                    $totalDaysInMonth = $now->daysInMonth;

                    $totalClicksTillDate = DB::table('clicks as c')
                        ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
                        ->whereBetween('c.click_ts', [$startOfMonth, $todayDate])
                        ->count();

                    $perDayClicks = $totalClicksTillDate / $todayDayNumber;

                    $totalCost = $totalClicksTillDate * ($plan->price);

                    $invoicePace = $perDayClicks * $totalDaysInMonth * ($plan->price);

                    $clicksPace = $perDayClicks * $totalDaysInMonth;
                } else {

                    $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');

                    $todayDate = $now->format('Y-m-d');

                    $todayDayNumber = $now->format('j');

                    $totalDaysInMonth = $now->daysInMonth;

                    $totalClicksTillDate = DB::table('clicks as c')
                        ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
                        ->whereBetween('c.click_ts', [$startOfMonth, $todayDate])
                        ->count();

                    $perDayClicks = $totalClicksTillDate / $todayDayNumber;

                    $totalCost = $totalClicksTillDate * ($plan->price);

                    $invoicePace = $perDayClicks * $totalDaysInMonth * ($plan->price);

                    $clicksPace = $perDayClicks * $totalDaysInMonth;
                }
            }
        }

        $metrics = DB::table('clicks as c')
            ->leftJoin('clicks_conversions as cc', 'cc.click_id', '=', 'c.id')
            ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
            ->leftJoin('partners as p', 'p.id', '=', 'pa.partner_id')
            ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
            ->whereBetween('c.click_ts', [$dateFrom, $dateTo])
            ->select(
                DB::raw('COUNT(c.id) as total_clicks'),
                DB::raw('COUNT(cc.id) as total_conversions'),
                DB::raw('(COUNT(cc.id) / COUNT(c.id)) * 100 as conversion_rate')
            )
            ->groupBy('p.id')
            ->first();

        // Ensure metrics exists
        if ($metrics) {
            $metrics->total_cost = $totalCost;
            $metrics->invoice_pace = $invoicePace;
            $metrics->clicks_pace = $clicksPace;
        }


        $availability_data = ProviderAvailabilityData::where('zoho_cust_id', $partner->zoho_cust_id)->first();
        $company_info = ProviderData::where('zoho_cust_id', $partner->zoho_cust_id)->first();

        $budget_cap = BudgetCapSettings::where('partner_id', $partner->id)->first();


        return view('partner.clicks-report', compact(
            'partner',
            'metrics',
            'chartData',
            'dateFrom',
            'dateTo',
            'daysInRange',
            'totalCost',
            'current_subscription',
            'company_info',
            'availability_data',
            'budget_cap',
            'is_daily_plan',
            'is_monthly_plan',
            'is_weekly_plan',
        ));
    }

    private function getDateRange($filter, $request, $now)
    {
        switch ($filter) {
            case 'mtd':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy()->subDay();
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy();
                break;
            case 'last_12_months':
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
            case 'last_6_months':
                $dateFrom = $now->copy()->subMonths(6);
                $dateTo = $now->copy();
                break;
            case 'last_3_months':
                $dateFrom = $now->copy()->subMonths(3);
                $dateTo = $now->copy();
                break;
            case 'last_1_month':
                $dateFrom = $now->copy()->subMonth();
                $dateTo = $now->copy();
                break;
            case 'last_7_days':
                $dateFrom = $now->copy()->subDays(7);
                $dateTo = $now->copy();
                break;
            case '-':
                $dateFrom = Carbon::parse($request->get('date_from'));
                $dateTo = Carbon::parse($request->get('date_to'));
                break;
            default:
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
        }

        return [$dateFrom, $dateTo];
    }

    private function getChartData($query, $dataSplit)
    {
        $chartData = [];

        if ($dataSplit === 'daily') {

            $results = $query->select(
                DB::raw('DATE(c.click_ts) as date'),
                DB::raw('COUNT(c.id) as total_clicks'),
                'a.domain_name as domain'
            )
                ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
                ->leftJoin('affiliates as a', 'a.id', '=', 'pa.affiliate_id')
                ->groupBy(DB::raw('DATE(c.click_ts)'), 'a.domain_name')
                ->orderBy(DB::raw('DATE(c.click_ts)'), 'asc')
                ->get();

            $domainClicks = [];

            foreach ($results as $result) {

                $formattedDate = Carbon::parse($result->date)->format('d M Y');

                if (!isset($domainClicks[$formattedDate])) {

                    $domainClicks[$formattedDate] = [];
                }
                $domainClicks[$formattedDate][$result->domain] = $result->total_clicks;
            }

            foreach ($domainClicks as $date => $domains) {
                $chartData[] = [
                    'date' => $date,
                    'total_clicks' => array_sum($domains),
                    'domain_clicks' => $domains,
                ];
            }
        } elseif ($dataSplit === 'weekly') {

            // Adjust the query to group by week (year and week number)
            $results = $query->select(
                DB::raw('YEAR(c.click_ts) as year'), // Extract the year
                DB::raw('WEEK(c.click_ts) as week'), // Extract the week number
                DB::raw('COUNT(c.id) as total_clicks'),
                DB::raw('MIN(click_ts) as from_date'),
                DB::raw(' MAX(click_ts) as to_date'), // Count clicks
                'a.domain_name as domain' // Get domain name
            )
                ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
                ->leftJoin('affiliates as a', 'a.id', '=', 'pa.affiliate_id')
                ->groupBy(DB::raw('YEAR(c.click_ts)'), DB::raw('WEEK(c.click_ts)'), 'a.domain_name') // Group by year and week
                ->orderBy(DB::raw('YEAR(c.click_ts)'), 'asc') // Order by year and week
                ->orderBy(DB::raw('WEEK(c.click_ts)'), 'asc') // Ensure proper weekly ordering
                ->get();

            $domainClicks = [];

            foreach ($results as $result) {
                $fromDate = Carbon::parse($result->from_date)->format('d M Y');
                $toDate = Carbon::parse($result->to_date)->format('d M Y');

                // Create a readable format for week by combining year and week number
                $formattedWeek = $fromDate . ' to ' . $toDate;

                if (!isset($domainClicks[$formattedWeek])) {
                    $domainClicks[$formattedWeek] = [];
                }
                $domainClicks[$formattedWeek][$result->domain] = $result->total_clicks;
            }

            foreach ($domainClicks as $week => $domains) {
                $chartData[] = [
                    'date' => $week, // Use the formatted week
                    'total_clicks' => array_sum($domains), // Sum up clicks for all domains
                    'domain_clicks' => $domains, // List clicks per domain
                ];
            }
        } else {

            // Adjust the query to group by month and year, and format the date as "YYYY-MM"
            $results = $query->select(
                DB::raw('DATE_FORMAT(c.click_ts, "%Y-%m") as month_year'), // Format as "YYYY-MM" (e.g., "2024-01")
                DB::raw('COUNT(c.id) as total_clicks'), // Count clicks
                'a.domain_name as domain' // Get domain name
            )
                ->leftJoin('partners_affiliates as pa', 'pa.id', '=', 'c.partners_affiliates_id')
                ->leftJoin('affiliates as a', 'a.id', '=', 'pa.affiliate_id')
                ->groupBy(DB::raw('DATE_FORMAT(c.click_ts, "%Y-%m")'), 'a.domain_name') // Group by formatted "YYYY-MM"
                ->orderBy(DB::raw('DATE_FORMAT(c.click_ts, "%Y-%m")'), 'asc') // Order by year and month
                ->get();

            $domainClicks = [];

            foreach ($results as $result) {

                // The formatted month-year string will be like "2024-01"
                $formattedMonth = \Carbon\Carbon::parse($result->month_year)->format('F Y'); // Convert to "January 2024"

                if (!isset($domainClicks[$formattedMonth])) {
                    $domainClicks[$formattedMonth] = [];
                }
                $domainClicks[$formattedMonth][$result->domain] = $result->total_clicks;
            }

            foreach ($domainClicks as $month => $domains) {
                $chartData[] = [
                    'date' => $month, // Display "January 2024" or similar
                    'total_clicks' => array_sum($domains), // Sum up clicks for all domains in that month
                    'domain_clicks' => $domains, // List clicks per domain
                ];
            }
        }


        return $chartData;
    }


    public function showClicksReportOld(Request $request)
    {
        $partnerId = Session::get('loginId');
        $partner = Partner::where('zoho_cust_id', $partnerId)->first();
        $is_daily_plan = false;
        $is_weekly_plan = false;
        $is_monthly_plan = false;

        $current_subscription = Subscriptions::where('zoho_cust_id', $partnerId)->where('status', 'live')->first();

        if ($current_subscription) {

            $current_plan = Plans::where('plan_id', $current_subscription->plan_id)->first();
            $current_plan_feature = Feature::where('plan_code', $current_plan->plan_code)->first();
            $is_monthly_plan = stripos($current_plan_feature->features_json['reporting'], 'monthly') !== false;
            $is_weekly_plan = stripos($current_plan_feature->features_json['reporting'], 'weekly') !== false;
            $is_daily_plan = stripos($current_plan_feature->features_json['reporting'], 'daily') !== false;
        }


        if (!$partner) {
            return redirect()->route('partner.reports')->withErrors('Partner not found');
        }

        $affiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

        if ($affiliateIds->isEmpty()) {

            return redirect()->route('partner.reports')->withErrors('No affiliates found for this partner.');
        }

        $query = Clicks::whereIn('partners_affiliates_id', $affiliateIds);


        $now = Carbon::now();
        $filter = $request->input('filter', 'mtd');
        $dataSplit = $request->input('data_split', 'daily');
        $dateFrom = null;
        $dateTo = null;

        if ($request->has('date_from')) {
            $dateFrom = Carbon::parse($request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $dateTo = Carbon::parse($request->get('date_to'));
        }

        switch ($filter) {
            case 'mtd':
                $dateFrom =  $now->copy()->startOfMonth();
                $dateTo =  $now->copy()->subDay();
                if ($dataSplit === 'monthly') {
                    $dataSplit = 'daily';
                }
                break;
            case 'this_month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy();
                if ($dataSplit === 'monthly') {
                    $dataSplit = 'daily';
                }
                break;
            case 'last_12_months':
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
            case 'last_6_months':
                $dateFrom = $now->copy()->subMonths(6);
                $dateTo = $now->copy();
                break;
            case 'last_3_months':
                $dateFrom = $now->copy()->subMonths(3);
                $dateTo = $now->copy();
                break;
            case 'last_1_month':
                $dateFrom =  $now->copy()->subMonth();
                $dateTo = $now->copy();
                break;
            case 'last_7_days':
                $dateFrom = $now->copy()->subDays(7);
                $dateTo = $now->copy();
                break;
            case '-':
                $dateFrom = Carbon::parse($request->get('date_from'));
                $dateTo = Carbon::parse($request->get('date_to'));
                break;
            default:
                $dateFrom = $now->copy()->subMonths(12);
                $dateTo = $now->copy();
                break;
        }

        $total_clicks = $query->count();
        $query->where('click_ts', '>=', $dateFrom)
            ->where('click_ts', '<=', $dateTo);
        $chartData = [];

        if ($dataSplit == 'daily') {
            $results = $query->select(DB::raw('DATE(click_ts) as date, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('DATE(click_ts)'))
                ->get();

            foreach ($results as $result) {
                $formattedDate = Carbon::parse($result->date)->format('d M Y');
                $chartData[] = [
                    'click_date' => $formattedDate,
                    'click_count' => $result->total_clicks,
                ];
            }
        } elseif ($dataSplit == 'weekly') {
            $results = $query->select(DB::raw('YEAR(click_ts) as year, WEEK(click_ts) as week, MIN(click_ts) as from_date, MAX(click_ts) as to_date, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'))
                ->get();

            foreach ($results as $result) {
                $fromDate = Carbon::parse($result->from_date)->format('d M Y');
                $toDate = Carbon::parse($result->to_date)->format('d M Y');
                $chartData[] = [
                    'click_date' => 'Week ' . $result->week . ', ' . $result->year . ' (' . $fromDate . ' to ' . $toDate . ')',
                    'click_count' => $result->total_clicks,
                ];
            }
        } else {
            $results = $query->select(DB::raw('YEAR(click_ts) as year, MONTH(click_ts) as month, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'))
                ->get();
            foreach ($results as $result) {
                $formattedDate = Carbon::createFromDate($result->year, $result->month)->format('M Y');
                $chartData[] = [
                    'click_date' => $formattedDate,
                    'click_count' => $result->total_clicks,
                ];
            }
        }

        $topN = $request->input('topN', 10);
        $topZipCodes = $this->getTopZipCodes($partner->id, $filter, $topN);

        $filterText = $this->getFilterText($filter);

        if ($request->has('download')) {
            $pdf = PDF::loadView('partner.clicks-report-pdf', compact('partner', 'chartData', 'topZipCodes', 'filterText', 'topN'));

            return $pdf->download('clicks_report.pdf');
        }

        return view('partner.clicks-report', compact('partner', 'chartData',  'topZipCodes', 'filterText', 'topN', 'filter', 'dataSplit', 'is_daily_plan', 'current_subscription', 'is_monthly_plan', 'is_weekly_plan', 'total_clicks'));
    }

    private function getTopZipCodes($partnerId, $filter, $topN)
    {
        $now = Carbon::now();
        $affiliateIds = PartnersAffiliates::where('partner_id', $partnerId)->pluck('id');

        $query = Clicks::select('intended_zip', DB::raw('COUNT(*) as total_clicks'))
            ->whereIn('partners_affiliates_id', $affiliateIds);

        switch ($filter) {
            case 'last_12_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(12));
                break;
            case 'last_6_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(6));
                break;
            case 'last_3_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(3));
                break;
            case 'last_1_month':
                $query->where('click_ts', '>=', $now->copy()->subMonth());
                break;
            case 'last_7_days':
                $query->where('click_ts', '>=', $now->copy()->subDays(7));
                break;
            default:
                $query->where('click_ts', '>=', $now->copy()->subMonths(12));
                break;
        }

        return $query->groupBy('intended_zip')
            ->orderByDesc('total_clicks')
            ->take($topN)
            ->get();
    }

    private function getFilterText($filter)
    {
        switch ($filter) {
            case 'last_12_months':
                return 'Last 12 Months';
            case 'last_6_months':
                return 'Last 6 Months';
            case 'last_3_months':
                return 'Last 3 Months';
            case 'last_1_month':
                return 'Last Month';
            case 'last_7_days':
                return 'Last 7 Days';
            default:
                return 'Last 12 Months';
        }
    }

    public function exportClicksReport(Request $request)
    {
        $partnerId = Session::get('loginId');
        $partner = Partner::where('zoho_cust_id', $partnerId)->first();
        $partnerAffiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

        // Handle filter and date range
        $filter = $request->input('filter', 'mtd');
        $dataSplit = $request->input('data_split', 'daily');
        $now = Carbon::now();
        [$dateFrom, $dateTo] = $this->getDateRange($filter, $request, $now);

        // Fetch the click data
        $query = DB::table('clicks as c')
            ->whereIn('c.partners_affiliates_id', $partnerAffiliateIds)
            ->whereBetween('c.click_ts', [$dateFrom, $dateTo]);

        // Get chart data
        $chartData = $this->getChartData($query, $dataSplit);
        $filterText = $this->getFilterText($filter);
        // Prepare the CSV output
        $csv = Writer::createFromFileObject(new SplTempFileObject(), 'w+');
        $csv->insertOne(['Clicks Report - ' . $filterText]);
        $csv->insertOne(['Date', 'Total Clicks']); // Column headers

        // Add data rows to the CSV
        foreach ($chartData as $data) {
            $row = [$data['date'], $data['total_clicks']];
            $csv->insertOne($row);
        }

        // Set CSV headers for download
        $filename = 'clearlink_clicks_report.csv';
        $response = new Response($csv->getContent());
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }


    public function exportClicksReportOld(Request $request)
    {
        $partnerId = Session::get('loginId');
        $partner = Partner::where('zoho_cust_id', $partnerId)->first();

        if (!$partner) {
            return redirect()->route('partner.reports')->withErrors('Partner not found');
        }

        $affiliateIds = PartnersAffiliates::where('partner_id', $partner->id)->pluck('id');

        $query = Clicks::whereIn('partners_affiliates_id', $affiliateIds);


        $now = Carbon::now();
        $filter = $request->input('filter', 'last_12_months');
        $dataSplit = $request->input('data_split', 'monthly');

        switch ($filter) {
            case 'last_12_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(12));
                break;
            case 'last_6_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(6));
                break;
            case 'last_3_months':
                $query->where('click_ts', '>=', $now->copy()->subMonths(3));
                break;
            case 'last_1_month':
                $query->where('click_ts', '>=', $now->copy()->subMonth());
                break;
            case 'last_7_days':
                $query->where('click_ts', '>=', $now->copy()->subDays(7));
                break;
            default:
                $query->where('click_ts', '>=', $now->copy()->subMonths(12));
                break;
        }

        if ($dataSplit == 'daily') {
            $results = $query->select(DB::raw('DATE(click_ts) as date, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('DATE(click_ts)'))
                ->orderBy(DB::raw('DATE(click_ts)'), 'desc')
                ->get();
        } elseif ($dataSplit == 'weekly') {
            $results = $query->select(DB::raw('YEAR(click_ts) as year, WEEK(click_ts) as week, MIN(click_ts) as from_date, MAX(click_ts) as to_date, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), WEEK(click_ts)'), 'desc')
                ->get();
        } else {
            $results = $query->select(DB::raw('YEAR(click_ts) as year, MONTH(click_ts) as month, COUNT(*) as total_clicks'))
                ->groupBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'))
                ->orderBy(DB::raw('YEAR(click_ts), MONTH(click_ts)'), 'desc')
                ->get();
        }

        // $topN = $request->input('topN', 10);
        // $topZipCodes = $query->select('intended_zip', DB::raw('COUNT(*) as total_clicks'))
        //     ->groupBy('intended_zip')
        //     ->orderByDesc('total_clicks')
        //     ->take($topN)
        //     ->get();

        $filterText = $this->getFilterText($filter);

        $response = new StreamedResponse(function () use ($results, $dataSplit, $filterText) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());

            // Write header
            $csv->insertOne(['Clicks Report - ' . $filterText]);
            if ($dataSplit == 'daily') {
                $csv->insertOne(['Date', 'Total Clicks']);
                foreach ($results as $result) {
                    $formattedDate = Carbon::parse($result->date)->format('d M Y');
                    $csv->insertOne([$formattedDate, $result->total_clicks]);
                }
            } elseif ($dataSplit == 'weekly') {
                $csv->insertOne(['Week', 'Total Clicks']);
                foreach ($results as $result) {
                    $fromDate = Carbon::parse($result->from_date)->format('d M Y');
                    $toDate = Carbon::parse($result->to_date)->format('d M Y');
                    $csv->insertOne(['Week ' . $result->week . ', ' . $result->year . ' (' . $fromDate . ' to ' . $toDate . ')', $result->total_clicks]);
                }
            } else {
                $csv->insertOne(['Month', 'Total Clicks']);
                foreach ($results as $result) {
                    $formattedDate = Carbon::createFromDate($result->year, $result->month)->format('M Y');
                    $csv->insertOne([$formattedDate, $result->total_clicks]);
                }
            }

            // $csv->insertOne([]);
            // $csv->insertOne(['Top Zip Codes']);
            // $csv->insertOne(['Rank', 'Zip Code', 'Total Clicks']);
            // foreach ($topZipCodes as $index => $zipCode) {
            //     $csv->insertOne([$index + 1, $zipCode->intended_zip, $zipCode->total_clicks]);
            // }

            $csv->output('clearlink_clicks_report.csv');
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="clicks_report.csv"');

        return $response;
    }
}
