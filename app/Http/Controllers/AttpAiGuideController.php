<?php

namespace App\Http\Controllers;

use App\Models\AttpAiGuideSetting;
use App\Models\Role;
use Illuminate\Http\Request;

class AttpAiGuideController extends Controller
{
    /**
     * Display ATTP AI Guide settings
     */
    public function settings()
    {
        $this->authorize('users.manage');

        $settings = AttpAiGuideSetting::first() ?? new AttpAiGuideSetting;
        $roles = Role::orderBy('name')->get();

        return view('system.attp-ai-guide.settings', compact('settings', 'roles'));
    }

    /**
     * Update ATTP AI Guide settings
     */
    public function updateSettings(Request $request)
    {
        $this->authorize('users.manage');

        foreach (['tawk_property_id', 'tawk_widget_id'] as $credential) {
            $value = $request->input($credential);

            if (is_string($value)) {
                $value = trim($value);
                $request->merge([$credential => $value === '' ? null : $value]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'enabled' => 'sometimes|boolean',
            'tawk_property_id' => ['nullable', 'string', 'max:255', 'regex:/\A[A-Za-z0-9_-]+\z/'],
            'tawk_widget_id' => ['nullable', 'string', 'max:255', 'regex:/\A[A-Za-z0-9_-]+\z/'],
            'show_to_authenticated_only' => 'sometimes|boolean',
            'show_to_guests' => 'sometimes|boolean',
            'targeted_user_roles' => 'nullable|array',
            'targeted_user_roles.*' => 'uuid|exists:roles,id',
            'welcome_message' => 'nullable|string|max:1000',
        ]);

        // HTML omits unchecked checkboxes and empty multi-selects. Normalize
        // them explicitly so true values can always be switched off or cleared.
        $validated['enabled'] = $request->boolean('enabled');
        $validated['show_to_authenticated_only'] = $request->boolean('show_to_authenticated_only');
        $validated['show_to_guests'] = $request->boolean('show_to_guests');
        $validated['targeted_user_roles'] = array_values(array_unique($validated['targeted_user_roles'] ?? []));

        $settings = AttpAiGuideSetting::first() ?? new AttpAiGuideSetting;
        $settings->fill($validated);
        $settings->save();

        return redirect()
            ->back()
            ->with('success', 'ATTP AI Guide settings updated successfully!');
    }

    /**
     * Test the Tawk widget
     */
    public function testWidget()
    {
        $this->authorize('users.manage');

        $settings = AttpAiGuideSetting::active();

        if (! $settings || ! $settings->enabled) {
            return response()->json(['error' => 'ATTP AI Guide is not enabled'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Test widget trigger sent. Check your page for the Tawk widget.',
            'settings' => [
                'property_id' => $settings->tawk_property_id,
                'widget_id' => $settings->tawk_widget_id,
            ],
        ]);
    }
}
