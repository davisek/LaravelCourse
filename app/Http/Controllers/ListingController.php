<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // All listings
    public function index() {
        return view('listings.index', [
            'listings' => Listing::latest()->
                filter(request(['tag', 'search']))->simplePaginate(4)
        ]);
    }
    // Show Single Listing
    public function show(Listing $listing) {
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    // Create Form
    public function create() {
        return view('listings.create');
    }

    // Store Listing Data
    public function store(Request $request) {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos','public');
        }

        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);

        //Session::flash('message', 'Listing Created');

        return redirect('/')->with('success', 'Listing created successfully!');
    }

    // Edit Form
    public function edit(Listing $listing) {
        return view('listings.edit', ['listing' => $listing]);
    }

    // Update Listing Data
    public function update(Request $resquest, Listing $listing) {

        // Make sure logged-in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        $formFields = $resquest->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($resquest->hasFile('logo')) {
            $formFields['logo'] = $resquest->file('logo')->store('logos','public');
        }

        $listing->update($formFields);

        //Session::flash('message', 'Listing Created');

        return back()->with('success', 'Listing created successfully!');
    }

    // Delete Listing
    public function destroy(Listing $listing) {

        // Make sure logged-in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        $listing->delete();
        return redirect('/')->with('success', 'Listing deleted successfully.');
    }

    // Manage Listing
    public function manage() {
        return view('listings.manage', [
            'listings' => auth()->user()->listings()->get()
        ]);
    }
}
