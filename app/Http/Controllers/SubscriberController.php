<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function subscribe(Request $request)
    {
        $validate = $request->validate([
            'email' => ['required', 'email', 'unique:subscribers,email'],
        ]);
        $subscriber = Subscriber::create([
            "email" => $validate['email']
        ]);
        return response()->json([
            "message" => "Hello subscriber",
            "email" => $subscriber->email
        ], 201);
    }
}
