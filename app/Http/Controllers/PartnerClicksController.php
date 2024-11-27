<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PartnerClicksView;

use Illuminate\Support\Facades\DB;


class PartnerClicksController extends Controller
{


    public function showPartnerClicks(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $month = $request->input('month');

        $year = $request->input('year');

        $search = $request->input('search');

        $query = DB::table('partner_clicks_view');

        if ($month) {

            $query->where('click_month', $month);
        }

        if ($year) {
            $query->where('click_year', $year);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('partner_company_name', 'like', "%{$search}%")
                    ->orWhere('primary_contact_email', 'like', "%{$search}%");
            });
        }

        $partnerClicks = $query->orderBy('click_year', 'desc')->orderBy('click_month', 'desc')->paginate($perPage);

        return view('admin.all-partner-clicks', compact('partnerClicks'));
    }
}
