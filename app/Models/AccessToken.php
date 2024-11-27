<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AccessToken extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::refreshToken();
    }

    public static function refreshToken()
    {
        $latestAccessToken = self::latest('created_at')->first();

        // Refresh the token if no token exists or if the token is older than 50 minutes
        if (!$latestAccessToken || $latestAccessToken->created_at->diffInMinutes(Carbon::now()) >= 50) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://accounts.zoho.com/oauth/v2/token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query([
                    'refresh_token' => env('REFRESH_TOKEN'),
                    'client_id' => env('CLIENT_ID'),
                    'client_secret' => env('CLIENT_SECRET'),
                    'redirect_uri' => env('REDIRECT_URI'),
                    'grant_type' => 'refresh_token'
                ])
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $data = json_decode($response);

            if (isset($data->access_token)) {
                // Clear all existing tokens
                self::query()->delete();

                // Save the new token
                $accessToken = new self();
                $accessToken->access_token = $data->access_token;
                $accessToken->save();
            } else {
                // Handle the error appropriately (e.g., log the error, throw an exception, etc.)
                throw new \Exception('Failed to refresh access token: ' . $response);
            }
        }
    }
}

