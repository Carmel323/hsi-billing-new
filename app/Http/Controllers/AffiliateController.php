<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Affiliates;
use App\Models\Partner;
use App\Models\PartnersAffiliates;
use FuncInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AffiliateController extends Controller
{
    public function getAffiliates(Request $request)
    {
        $query = Affiliates::query();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {

            $query->whereDate('affiliates.created_at', '>=', $startDate)
                ->whereDate('affiliates.created_at', '<=', $endDate);
        }

        if ($request->has('search')) {

            $search = $request->input('search');

            $query->where(function ($q) use ($search) {

                $q->where('isp_affiliate_id', 'LIKE', "%{$search}%")
                    ->orWhere('domain_name', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 10;

        $affiliates = $query->orderByDesc('id')->paginate($perPage);
        return view('admin.affiliates', compact('affiliates'));
    }

    public function addAffiliate(Request $request)
    {
        $existingAffiliate = Affiliates::where('isp_affiliate_id', $request->affiliate_id)->first();

        if ($existingAffiliate) {

            return back()->with('fail', 'Affiliate ID already exists');
        }

        $affiliate = new Affiliates();
        $affiliate->isp_affiliate_id = $request->affiliate_id;
        $affiliate->domain_name = $request->domain_name;
        $affiliate->save();

        $partners = Partner::all();

        $affiliateId = $affiliate->id;

        foreach ($partners as $partner) {
            $partner_affiliate = new PartnersAffiliates();
            $partner_affiliate->affiliate_id = $affiliateId;
            $partner_affiliate->partner_id = $partner->id;
            $partner_affiliate->save();
        }


        return back()->with('success', 'Affiliate Added Successfully');
    }

    public function removeAffiliate(Request $request)
    {
        $request->validate([
            'affiliate_id' => 'required',
            'id' => 'required|integer|exists:affiliates,id',
        ]);


        $affiliate = Affiliates::where('id', $request->id)
            ->where('isp_affiliate_id', $request->affiliate_id)
            ->first();

        if (!$affiliate) {

            return back()->with('error', 'Affiliate not found');
        }

        $affiliate->delete();

        return back()->with('success', 'Affiliate Removed Successfully');
    }

    public function editAffiliate(Request $request)
    {
        $affiliate = Affiliates::where('id', $request->id)->first();

        $affiliate->isp_affiliate_id = $request->affiliate_id;
        $affiliate->domain_name = $request->domain_name;
        $affiliate->save();
        return back()->with('success', 'Affiliate Updated Successfully');
    }

    public function deleteAffiliate()
    {
        $id = Route::getCurrentRoute()->id;

        $affiliate = Affiliates::where('id', $id)->first();
        $affiliate->delete();

        return back()->with('success', 'Affiliate Deleted Successfully');
    }
}
