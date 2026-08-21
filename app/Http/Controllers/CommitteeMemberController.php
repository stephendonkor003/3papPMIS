<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\User;
use Illuminate\Http\Request;

class CommitteeMemberController extends Controller
{
    public function index()
    {
        $members = CommitteeMember::with('user', 'committee')->get();
        return view('committee_members.index', compact('members'));
    }

    public function create()
    {
        $users = User::all();
        $committees = Committee::all();
        return view('committee_members.create', compact('users', 'committees'));
    }

    public function show(CommitteeMember $committeeMember)
    {
        $committeeMember->load(['user', 'committee']); // eager load relationships
        return view('committee_members.show', compact('committeeMember'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'user_id' => 'required|exists:users,id'
        ]);

        CommitteeMember::create($request->all());
        return redirect()->route('committee-members.index')->with('success', 'Member added.');
    }

    public function destroy(CommitteeMember $committeeMember)
    {
        $committeeMember->delete();
        return redirect()->route('committee-members.index')->with('success', 'Member removed.');
    }
}
