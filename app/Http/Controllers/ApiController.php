<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiController extends Controller
{

    public function index(Request $request){
        if (Session::get('role') === 'SuperAdmin') {
            $clients = Client::where('revoked', false)->paginate(30);
            return view('admin.api-clients', compact('clients'));
        }
        return response('Forbidden', 403);
    }

    public function store(Request $request) {
        if (Session::get('role') !== 'SuperAdmin') {
            return response('Forbidden', 403);
        }
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'expires_in_days' => 'nullable|string|min:1',
        ]);

        // Check if the input is a number and convert it to an integer type
        if ($request->input('expires_in_days') && !is_numeric($request->input('expires_in_days'))) {
            return response('Invalid input', 400);
        }
        $expiresInDays = $request->input('expires_in_days') ? intval($request->input('expires_in_days')) : null;


        $plainSecret = Str::random(40);

        // Create the client
        $result = Client::create([
            'name' => $request->name,
            'redirect' => 'http://localhost', // placeholder value, not used for machine-to-machine communication
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'secret' => $plainSecret
        ]);
        
        return response()->json([
            'name' => $request->name,
            'client_id' => $result->id,
            'client_secret' => $plainSecret,
            'expires_in_days' => $expiresInDays,
        ]);
    }

    public function revoke(Request $request) {
        // Validate input
        $request->validate([
            'client_id' => 'required|string'
        ]);

        // Find the token
        try{
          $client = Client::find($request->input('client_id'));
        } catch (\Exception $e) {
          return response('Client not found', 404);
        }

        // Revoke the token
        $client->revoked = true;
        $client->save();

        return response('Token revoked', 200);
    }
}