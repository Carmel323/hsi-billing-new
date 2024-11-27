<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DeleteRecordsController extends Controller
{

    public function showDeleteForm()
    {
        return view('delete_records');
    }

    public function deleteRecords()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('subscriptions')->delete();
        DB::table('credit_notes')->delete();
        DB::table('invoices')->delete();
        DB::table('payment_methods')->delete();
        DB::table('add_ons')->delete();
        DB::table('partner_addresses')->delete();
        DB::table('partner_users')->delete();
        DB::table('partners')->delete();
        DB::table('supports')->delete();
        DB::table('terms')->delete();
        DB::table('features')->delete();
        DB::table('plans')->delete();
        DB::table('leads')->delete();
        // DB::table('affiliates')->delete();
        DB::table('partners_affiliates')->delete();
        DB::table('clicks_email_log')->delete();


        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json(['message' => 'Records deleted successfully.'], 200);
    }
}
