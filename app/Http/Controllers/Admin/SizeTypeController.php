<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeType;
use Illuminate\Http\Request;

class SizeTypeController extends Controller
{
    public function index()
    {
        $sizeTypes = SizeType::orderBy('id', 'desc')->get();

        return view('admin.size-types.index', compact('sizeTypes'));
    }

    public function create()
    {
        return view('admin.size-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
        ]);

        SizeType::create([
            'length' => $data['length'],
            'width' => $data['width'],
        ]);

        return redirect()->route('admin.size-types.index')
            ->with('success', 'Size Type created successfully.');
    }

    public function edit(SizeType $sizeType)
    {
        return view('admin.size-types.edit', compact('sizeType'));
    }

    public function update(Request $request, SizeType $sizeType)
    {
        $data = $request->validate([
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
        ]);

        $sizeType->update([
            'length' => $data['length'],
            'width' => $data['width'],
        ]);

        return redirect()->route('admin.size-types.index')
            ->with('success', 'Size Type updated successfully.');
    }

    public function destroy(SizeType $sizeType)
    {
        $sizeType->delete();

        return redirect()->route('admin.size-types.index')
            ->with('success', 'Size Type deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'size_type_ids' => 'required|string',
        ]);

        $sizeTypeIds = json_decode($request->size_type_ids);
        
        if (!is_array($sizeTypeIds) || empty($sizeTypeIds)) {
            return back()->with('error', 'No size types selected for deletion.');
        }

        // Validate that all IDs exist
        $existingIds = SizeType::whereIn('id', $sizeTypeIds)->pluck('id')->toArray();
        $invalidIds = array_diff($sizeTypeIds, $existingIds);
        
        if (!empty($invalidIds)) {
            return back()->with('error', 'Some selected size types do not exist.');
        }

        SizeType::whereIn('id', $sizeTypeIds)->delete();

        return redirect()->route('admin.size-types.index')
            ->with('success', count($sizeTypeIds) . ' size type(s) deleted successfully.');
    }
}
