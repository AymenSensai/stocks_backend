<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Client as Google_Client;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        $idToken = $request->input('id_token');

        if (!$idToken) {
            return response()->json(['error' => 'ID Token is missing'], 400);
        }

        try {
            // Initialize Google Client
            $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

            // Verify the ID Token
            $payload = $client->verifyIdToken($idToken);

            if ($payload) {
                // ID Token is valid; extract user information
                $userId = $payload['sub'];
                $email = $payload['email'];
                $name = $payload['name'];

                // Perform further actions, like user creation or login
                return response()->json([
                    'message' => 'Google login successful',
                    'data' => compact('userId', 'email', 'name')
                ]);
            } else {
                return response()->json(['error' => 'Invalid ID Token'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
