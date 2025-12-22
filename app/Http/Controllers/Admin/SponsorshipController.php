<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Sponsorship::with('exhibition');
        
        // Filter by exhibition
        if ($request->has('exhibition_id') && $request->exhibition_id) {
            $query->where('exhibition_id', $request->exhibition_id);
        }
        
        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $sponsorships = $query->orderBy('exhibition_id')->orderBy('display_order')->paginate(20);
        $exhibitions = Exhibition::where('status', 'active')->get();
        
        return view('admin.sponsorships.index', compact('sponsorships', 'exhibitions'));
    }
    
    public function create()
    {
        $exhibitions = Exhibition::where('status', 'active')->get();
        return view('admin.sponsorships.create', compact('exhibitions'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deliverables' => 'required|array|min:1',
            'deliverables.*' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tier' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'max_available' => 'nullable|integer|min:1',
            'display_order' => 'nullable|integer|min:0',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('sponsorships', 'public');
        }
        
        $validated['is_active'] = $request->has('is_active');
        
        Sponsorship::create($validated);
        
        return redirect()->route('admin.sponsorships.index')
            ->with('success', 'Sponsorship package created successfully.');
    }
    
    public function show($id)
    {
        $sponsorship = Sponsorship::with(['exhibition', 'bookings.user'])->findOrFail($id);
        return view('admin.sponsorships.show', compact('sponsorship'));
    }
    
    public function edit($id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        $exhibitions = Exhibition::where('status', 'active')->get();
        return view('admin.sponsorships.edit', compact('sponsorship', 'exhibitions'));
    }
    
    public function update(Request $request, $id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        
        $validated = $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deliverables' => 'required|array|min:1',
            'deliverables.*' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tier' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'max_available' => 'nullable|integer|min:1',
            'display_order' => 'nullable|integer|min:0',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($sponsorship->image) {
                Storage::disk('public')->delete($sponsorship->image);
            }
            $validated['image'] = $request->file('image')->store('sponsorships', 'public');
        } else {
            unset($validated['image']);
        }
        
        $validated['is_active'] = $request->has('is_active');
        
        $sponsorship->update($validated);
        
        return redirect()->route('admin.sponsorships.index')
            ->with('success', 'Sponsorship package updated successfully.');
    }
    
    public function destroy($id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        
        // Check if there are any bookings
        if ($sponsorship->bookings()->count() > 0) {
            return back()->with('error', 'Cannot delete sponsorship with existing bookings.');
        }
        
        // Delete image if exists
        if ($sponsorship->image) {
            Storage::disk('public')->delete($sponsorship->image);
        }
        
        $sponsorship->delete();
        
        return redirect()->route('admin.sponsorships.index')
            ->with('success', 'Sponsorship package deleted successfully.');
    }
    
    public function toggleStatus($id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        $sponsorship->update(['is_active' => !$sponsorship->is_active]);
        
        return back()->with('success', 'Sponsorship status updated successfully.');
    }
}

