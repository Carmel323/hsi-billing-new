<?php

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\CreditNotes;
use App\Models\Partner;
use App\Models\ProviderAvailabilityData;
use App\Models\ProviderData;
use App\Models\Refund;
use App\Models\Subscriptions;
use App\Models\Support;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


class CreditNotesController extends Controller
{
    public function getCreditNotes(Request $request)
    {
        if (Session::has('loginPartner')) {

            try {

                $partnerId = Session::get('loginId');

                $query = DB::table('credit_notes')
                    ->where('zoho_cust_id', $partnerId);

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {
                    $query->whereDate('credit_notes.created_at', '>=', $startDate)
                        ->whereDate('credit_notes.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('creditnote_number', 'LIKE', "%{$search}%")
                            ->orWhere('credited_amount', 'LIKE', "%{$search}%")
                            ->orWhere('balance', 'LIKE', "%{$search}%");
                    });
                }

                $query->orderByDesc('creditnote_number'); // Order by creditnote_number in descending order

                $perPage = $request->input('per_page', 10);

                $creditnotes = $query->paginate($perPage);

                $creditnotesArray = $creditnotes->items();

                $zohoCustIds = array_unique(array_column($creditnotesArray, 'zoho_cust_id'));

                $partners = DB::table('partners')->whereIn('zoho_cust_id', $zohoCustIds)->get()->keyBy('zoho_cust_id');

                foreach ($creditnotesArray as $creditnote) {

                    if (isset($partners[$creditnote->zoho_cust_id])) {

                        $creditnote->partner = $partners[$creditnote->zoho_cust_id];
                    } else {

                        $creditnote->partner = null;
                    }
                }
                $showModal = false;
                $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();
                $availability_data = ProviderAvailabilityData::where('zoho_cust_id', Session::get('loginId'))->first();
                $company_info = ProviderData::where('zoho_cust_id', Session::get('loginId'))->first();
                // if ($availability_data === null || $company_info === null) {
                //     $showModal = true;
                // }


                return view('partner.creditnotes', compact('creditnotes', 'showModal', 'availability_data', 'company_info'));
            } catch (Exception $e) {

                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function refund(Request $request, AccessToken $token)
    {
        $subscription = Subscriptions::where('zoho_cust_id', Session::get('loginId'))->first();
        $partner = Partner::where('zoho_cust_id', Session::get('loginId'))->first();

        if ($request->balance <= $request->refund_amount) {
            return back()->with('fail', 'Your refund cannot be greater than your balance');
        } else {

            $support = new Support();
            $support->date = date('d-M-Y');
            $support->request_type = 'Refund';
            $support->subscription_number = $subscription->subscription_number;
            $support->message = $request->reason;
            $support->status = "open";
            $support->zoho_cust_id = $partner->zoho_cust_id;
            $support->attributes = [
                'creditnote_id' => $request->id,
                'balance_amount' => $request->balance_amount,
                'refund_amount' => $request->refund_amount,
            ];
            $support->save();
            return back()->with('success', 'Your request has been recorded. We will get back to you shortly!');
        }
    }

    public function filter(Request $request)
    {
        $credits = CreditNotes::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->get();
        return view('partner.creditnotes', compact('credits'));
    }

    public function perPage(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $credits = CreditNotes::paginate($perPage);
        return view('partner.creditnotes', compact('credits'));
    }

    public function getCreditNotesOld(Request $request)
    {
        if (Session::has('loginPartner')) {
            try {
                $partnerId = Session::get('loginId');

                $query = DB::table('credit_notes')
                    ->where('zoho_cust_id', $partnerId);

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {

                    $query->whereDate('credit_notes.created_at', '>=', $startDate)
                        ->whereDate('credit_notes.created_at', '<=', $endDate);
                }

                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('creditnote_number', 'LIKE', "%{$search}%")
                            ->orWhere('credited_amount', 'LIKE', "%{$search}%")
                            ->orWhere('balance', 'LIKE', "%{$search}%");
                    });
                }

                $query->orderByDesc('creditnote_number'); // Order by creditnote_number in descending order

                $perPage = $request->input('per_page', 10);

                $creditnotes = $query->paginate($perPage);

                $creditnotesArray = $creditnotes->items();

                foreach ($creditnotesArray as $creditnote) {

                    $creditnote->partner = DB::table('partners')->where('zoho_cust_id', $creditnote->zoho_cust_id)->first();
                }
                return view('partner.creditnotes', compact('creditnotes'));
            } catch (Exception $e) {
                return redirect('/logout')->with('fail', 'You are logged out! Try to login again.');
            }
        }
    }

    public function viewCreditNote()
    {
        $creditnote_id = Route::getCurrentRoute()->id;
        $creditnote_url = env('CREDITNOTES_URL');
        $token1 = AccessToken::latest('created_at')->first();
        $access_token = $token1->access_token;
        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => [
                'Authorization' => ('Zoho-oauthtoken ' . $access_token),
                'X-com-zoho-subscriptions-organizationid' => env('ORGANIZATION_ID'),
            ]
        ];

        $creditnote_pdf_url = $creditnote_url . $creditnote_id . "?accept=pdf";

        $res = $client->request(
            'GET',
            $creditnote_url . $creditnote_id . "?accept=pdf",
            $options
        );

        $response = (string) $res->getBody();

        if ($response) {
            // Get the response content (PDF binary data)
            $pdfContent = $response;

            // Return the PDF as a downloadable response
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="creditnote.pdf"');
        } else {
            // Handle the case when the request fails
            return response()->json(['error' => 'Failed to download PDF']);
        }
    }
}
