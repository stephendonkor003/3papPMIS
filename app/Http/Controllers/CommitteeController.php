<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;

class CommitteeController extends Controller
{
    public function index()
    {
        $committees = Committee::with(['project', 'chairperson', 'members'])->paginate(10); // ✅

        return view('committees.index', compact('committees'));
    }

    public function create()
{
    $projects = Project::all();
    $users = User::all(); // ✅ make sure this is added

    return view('committees.create', compact('projects', 'users'));
}


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'project_id' => 'required|exists:projects,id'
        ]);

        Committee::create($request->all());
        return redirect()->route('committees.index')->with('success', 'Committee created.');
    }

    public function show(Committee $committee)
    {
        return view('committees.show', compact('committee'));
    }

    public function edit(Committee $committee)
    {
        $projects = Project::all();
        $users = User::all(); // ✅ add this

        return view('committees.edit', compact('committee', 'projects', 'users'));
    }


    public function update(Request $request, Committee $committee)
    {
        $committee->update($request->all());
        return redirect()->route('committees.index')->with('success', 'Committee updated.');
    }

    public function destroy(Committee $committee)
    {
        $committee->delete();
        return redirect()->route('committees.index')->with('success', 'Committee deleted.');
    }
}
