<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $prefs = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json([
            'status' => 'success',
            'data' => $prefs
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'notify_via_email' => 'boolean',
            'notify_via_sms' => 'boolean',
            'notify_via_push' => 'boolean',
            'phone_number' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);

        $prefs = NotificationPreference::firstOrNew(['user_id' => $request->user()->id]);

        $prefs->fill($request->only([
            'notify_via_email',
            'notify_via_sms',
            'notify_via_push',
            'phone_number',
            'fcm_token',
        ]));

        $prefs->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Preferences updated',
            'data' => $prefs
        ]);
    }
}
