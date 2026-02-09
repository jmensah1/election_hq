<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormSubmitted;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        // Send email to admin
        Mail::to('joseph.mensah@jbmensah.com')->queue(new ContactFormSubmitted($validated));

        return back()->with('success', 'Thank you for contacting us! We will get back to you shortly.');
    }
}
