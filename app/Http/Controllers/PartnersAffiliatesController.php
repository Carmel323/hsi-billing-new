<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PartnersAffiliates;

class PartnersAffiliatesController extends Controller
{
    public function edit()
    {
        $partnerAffiliates = PartnersAffiliates::all();

        return view('admin.partners-affiliates-table-edit', compact('partnerAffiliates'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'old_id' => 'required|integer',
            'partner_id' => 'required|integer',
            'affiliate_id' => 'required|integer'
        ]);

        $oldId = $request->input('old_id');

        $newId = $request->input('id');

        DB::transaction(function () use ($oldId, $newId, $request) {

            $partnerAffiliate = PartnersAffiliates::findOrFail($oldId);

            $partnerAffiliate->partner_id = $request->input('partner_id');

            $partnerAffiliate->affiliate_id = $request->input('affiliate_id');
            $partnerAffiliate->save();

            DB::table('partners_affiliates')
                ->where('id', $oldId)
                ->update(['id' => $newId]);
        });

        $partnerAffiliates = PartnersAffiliates::all();

        return view('admin.partners-affiliates-table-edit', compact('partnerAffiliates'))->with('success', 'Partner Affiliate updated successfully');
    }
}
