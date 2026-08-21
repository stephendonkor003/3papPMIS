<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicCheckController extends Controller
{
    /**
     * The legacy public consortium-evaluation lookup is not registered in the
     * route table and relied on a retired applicant evaluation schema.
     */
    public function index(): never
    {
        $this->retired();
    }

    public function search(Request $request): never
    {
        $this->retired();
    }

    public function sendEmail(Request $request): never
    {
        $this->retired();
    }

    private function retired(): never
    {
        abort(410, 'The public consortium evaluation lookup has been retired.');
    }
}
