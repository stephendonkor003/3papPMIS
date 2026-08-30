<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->with('status', 'Self-service registration is not available. Contact info@3pap.africa if you need PMIS access.');
    }

    /**
     * Keep the controller safe if a registration POST route is ever restored
     * accidentally. Accounts are provisioned only through managed workflows.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->create();
    }
}
