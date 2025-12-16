<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ServiceConfigController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        $services = $query->latest()->paginate(20);

        return view('admin.services.config', compact('services'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Service creation attempt', [
                'request_data' => $request->all(),
                'has_is_active' => $request->has('is_active'),
                'is_active_value' => $request->input('is_active')
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:5120',
                'is_active' => 'nullable',
            ]);

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('services', 'public');
            }

            // Handle checkbox: if present (checked), set to true, otherwise false
            $validated['is_active'] = $request->has('is_active') ? true : false;

            Log::info('Validated data before create', ['validated' => $validated]);

            $service = Service::create($validated);
            
            Log::info('Service created successfully', ['service_id' => $service->id]);
            
            return redirect()->route('admin.services.config')->with('success', 'Service created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Service creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Failed to create service: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        
        // Return JSON if requested via AJAX
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'image' => $service->image,
                'is_active' => $service->is_active,
            ]);
        }
        
        return back()->with('info', 'Service details');
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
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
