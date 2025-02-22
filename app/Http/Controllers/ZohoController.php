<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ZohoController extends Controller
{
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'deal_name' => 'required|string',
            'deal_stage' => 'required|string',
            'account_name' => 'required|string',
            'account_website' => 'nullable|url',
            'account_phone' => 'nullable|string',
        ]);

        // Get Zoho Access Token
        $zohoAccessToken = $this->getZohoAccessToken();

        if (!$zohoAccessToken) {
            return response()->json(['error' => 'Unable to retrieve Zoho access token'], 500);
        }

        // Create Account in Zoho CRM with headers and disabled SSL verification
        $accountResponse = Http::withOptions([
            'verify' => false
        ])->withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $zohoAccessToken,
            'Content-Type' => 'application/json'
        ])->post('https://www.zohoapis.eu/crm/v2/Accounts', [
            'data' => [[
                'Account_Name' => $request->account_name,
                'Website' => $request->account_website,
                'Phone' => $request->account_phone,
            ]]
        ]);

        $accountData = $accountResponse->json();

        if ($accountData['data'][0]['code'] !== "SUCCESS") {
            return response()->json(['error' => 'Failed to create account', 'details' => $accountData], 500);
        }
        $accountId = $accountData['data'][0]['details']['id'];

        // Create Deal in Zoho CRM with headers and disabled SSL verification
        $dealResponse = Http::withOptions([
            'verify' => false
        ])->withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $zohoAccessToken,
            'Content-Type' => 'application/json'
        ])->post('https://www.zohoapis.eu/crm/v2/Deals', [
            'data' => [[
                'Deal_Name' => $request->deal_name,
                'Stage' => $request->deal_stage,
                'Account_Name' => $request->account_name,
                'id' =>  $request->account_name,
            ]]
        ]);

        $dealData = $dealResponse->json();
        if ($dealData['data'][0]['code'] !== "SUCCESS") {
            return response()->json(['error' => 'Failed to create deal', 'details' => $dealResponse], 500);
        }

        return response()->json(['message' => 'Deal and account created successfully']);
    }

    private function getZohoAccessToken()
    {
        // Check if access token is cached
        if (Cache::has('zoho_access_token')) {
            return Cache::get('zoho_access_token');
        }

        // Request a new access token using the refresh token
        $response = Http::withOptions([
            'verify' => false
        ])->asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'refresh_token' =>env('ZOHO_REFRESH_TOKEN'),
            'client_id' => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
            'redirect_uri' =>env('APP_URL'),
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            error_log($data['access_token']);
            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in']; // Typically 3600 seconds

            // Cache the access token for its duration minus a buffer (e.g., 5 minutes)
            Cache::put('zoho_access_token', $accessToken, $expiresIn - 300);

            return $accessToken;
        }

        // Log error or handle it as needed
        return null;
    }
}
