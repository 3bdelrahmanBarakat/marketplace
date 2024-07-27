<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Mail;

class ContactUsController extends Controller
{
    public function sendInquiry(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ];

        Mail::to(env('MAIL_USERNAME'))->send(new ContactFormMail($data));

        return response()->json(['message' => 'Your message has been sent successfully.'], 200);
    }
}
