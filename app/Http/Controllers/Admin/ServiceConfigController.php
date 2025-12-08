<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceConfigController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('exhibition');

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('exhibition_id') && $request->exhibition_id) {
            $query->where('exhibition_id', $request->exhibition_id);
        }

        $services = $query->latest()->paginate(20);
        $exhibitions = Exhibition::all();
        $categories = Service::distinct()->pluck('category')->filter();

        return view('admin.services.config', compact('services', 'exhibitions', 'categories'));
    }

    public function addCategory(Request $request)
    {
        // Category is managed per service, not separately
        return back()->with('info', 'Categories are managed per service.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string|max:255',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date|after:available_from',
            'image' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        Service::create($validated);
        return redirect()->route('admin.services.config')->with('success', 'Service created successfully.');
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        
        // Return JSON if requested via AJAX
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($service);
        }
        
        return back()->with('info', 'Service details');
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate([
            'exhibition_id' => 'required|exists:exhibitions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string|max:255',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date|after:available_from',
            'image' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);
        return redirect()->route('admin.services.config')->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $service->delete();
        return redirect()->route('admin.services.config')->with('success', 'Service deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one service.');
        }

        switch ($action) {
            case 'activate':
                Service::whereIn('id', $ids)->update(['is_active' => true]);
                return back()->with('success', 'Services activated successfully.');
            case 'deactivate':
                Service::whereIn('id', $ids)->update(['is_active' => false]);
                return back()->with('success', 'Services deactivated successfully.');
            case 'delete':
                $services = Service::whereIn('id', $ids)->get();
                foreach ($services as $service) {
                    if ($service->image) {
                        Storage::disk('public')->delete($service->image);
                    }
                }
                Service::whereIn('id', $ids)->delete();
                return back()->with('success', 'Services deleted successfully.');
            default:
                return back()->with('error', 'Invalid action.');
        }
    }
}
