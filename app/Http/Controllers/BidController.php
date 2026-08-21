<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function index()
    {
        return $this->redirectToCurrentProcurementPortal();
    }

    public function create()
    {
        return $this->redirectToCurrentProcurementPortal();
    }

    public function store(Request $request)
    {
        abort(410, 'The legacy bid workflow has been retired. Use the procurement portal.');
    }

    public function show(Bid $bid)
    {
        return $this->redirectToCurrentProcurementPortal();
    }

    public function edit(Bid $bid)
    {
        return $this->redirectToCurrentProcurementPortal();
    }

    public function update(Request $request, Bid $bid)
    {
        abort(410, 'The legacy bid workflow has been retired. Use the procurement portal.');
    }

    public function destroy(Bid $bid)
    {
        abort(410, 'The legacy bid workflow has been retired. Use the procurement portal.');
    }

    private function redirectToCurrentProcurementPortal()
    {
        return redirect()
            ->route('public.procurement.index')
            ->with('info', 'Bids are now managed through the procurement portal.');
    }
}
