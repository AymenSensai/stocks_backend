<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the contacts for the authenticated user.
     */
    public function index()
    {
        $contacts = auth()->user()->contacts;

        return response()->json([
            'message' => 'Contacts retrieved successfully',
            'data' => $contacts
        ], 200);
    }

    /**
     * Store a newly created contact for the authenticated user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'contact_type' => 'required|in:Customer,Vendor',
        ]);

        $contact = auth()->user()->contacts()->create($validated);

        return response()->json([
            'message' => 'Contact created successfully',
            'data' => $contact
        ], 201);
    }

    /**
     * Display the specified contact for the authenticated user.
     */
    public function show($id)
    {
        $contact = auth()->user()->contacts()->find($id);

        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Contact retrieved successfully',
            'data' => $contact
        ], 200);
    }

    /**
     * Update the specified contact for the authenticated user.
     */
    public function update(Request $request, $id)
    {
        $contact = auth()->user()->contacts()->find($id);

        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:15',
            'contact_type' => 'sometimes|required|in:Customer,Vendor',
        ]);

        $contact->update($validated);

        return response()->json([
            'message' => 'Contact updated successfully',
            'data' => $contact
        ], 200);
    }

    /**
     * Remove the specified contact for the authenticated user.
     */
    public function destroy($id)
    {
        $contact = auth()->user()->contacts()->find($id);

        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found'
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'message' => 'Contact deleted successfully'
        ], 200);
    }
}
