<?php

namespace App\Http\Controllers;

use App\Models\PartnerInformationRequest;
use App\Mail\PartnerRequestResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PartnerRequestManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:partner.requests.manage']);
    }

    /**
     * Display a listing of all partner information requests (admin view)
     */
    public function index()
    {
        $requests = PartnerInformationRequest::with([
            'funder',
            'programFunding.program',
            'requester',
            'responder'
        ])
            ->latest()
            ->get();

        return view('finance.partner-requests.index', compact('requests'));
    }

    /**
     * Display the specified information request
     */
    public function show($id)
    {
        $request = PartnerInformationRequest::with([
            'funder.portalUser',
            'programFunding.program',
            'requester',
            'responder'
        ])->findOrFail($id);

        return view('finance.partner-requests.show', compact('request'));
    }

    /**
     * Respond to a partner information request
     */
    public function respond(Request $request, $id)
    {
        $this->middleware('permission:partner.requests.respond');

        $infoRequest = PartnerInformationRequest::findOrFail($id);

        $validated = $request->validate([
            'status'   => 'required|in:in_progress,completed,rejected',
            'response' => 'required|string|min:10',
        ]);

        $infoRequest->update([
            'status'        => $validated['status'],
            'response'      => $validated['response'],
            'responded_by'  => Auth::id(),
            'responded_at'  => now(),
        ]);

        // Send email notification to partner
        try {
            Mail::to($infoRequest->requester->email)
                ->send(new PartnerRequestResponse($infoRequest));
        } catch (\Exception $e) {
            \Log::error('Failed to send partner request response email: ' . $e->getMessage());
        }

        return back()->with('success', 'Response sent successfully.');
    }
}
