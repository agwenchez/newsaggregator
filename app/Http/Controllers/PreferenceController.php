<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    /**
     * List all preferences for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            try {
                $preferences = $request->user()->preferences;
                return response()->json(['preferences' => $preferences], 200);
            } catch (\Exception $e) {
                \Log::error('Error fetching preferences: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to fetch preferences' . $user], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error fetching preferences: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to register user here buana.'], 500);
        }
    }

    /**
     * Store a new preference for the authenticated user.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'source' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'author' => 'nullable|string|max:255',
            ]);

            $preference = $request->user()->preferences()->create($request->all());

            return response()->json(['message' => 'Preference created', 'preference' => $preference], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating preference: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create preference.'], 500);
        }
    }

    /**
     * Update an existing preference for the authenticated user.
     */
    public function update(Request $request, $id)
    {
        try {

            $preference = $request->user()->preferences()->findOrFail($id);

            $request->validate([
                'source' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'author' => 'nullable|string|max:255',
            ]);

            $preference->update($request->all());

            return response()->json(['message' => 'Preference updated', 'preference' => $preference], 200);
        } catch (\Exception $e) {
            \Log::error('Error updating preference: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update preference.'], 500);
        }
    }

    /**
     * Delete a preference for the authenticated user.
     */
    public function destroy(Request $request, $id)
    {
        try {

            $preference = $request->user()->preferences()->findOrFail($id);
            $preference->delete();

            return response()->json(['message' => 'Preference deleted'], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting prefernce: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete preferences.'], 500);
        }
    }
}
