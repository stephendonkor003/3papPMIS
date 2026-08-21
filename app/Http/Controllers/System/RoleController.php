<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('system.roles.index', [
            'roles' => Role::with('permissions')->get(),
        ]);
    }

    public function create()
    {
        return view('system.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        Role::create($request->only('name', 'description'));

        return redirect()
            ->route('system.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * ✅ ADD THIS METHOD
     */
    public function edit(Role $role)
    {
        return view('system.roles.edit', [
            'role' => $role,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($request->only('name', 'description'));

        return redirect()
            ->route('system.roles.index')
            ->with('success', 'Role updated successfully.');
    }
}