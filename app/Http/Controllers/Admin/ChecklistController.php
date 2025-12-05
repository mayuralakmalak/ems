<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\Exhibition;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = ChecklistItem::with('exhibition');

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('exhibition_id') && $request->exhibition_id) {
            $query->where('exhibition_id', $request->exhibition_id);
        }

        $checklistItems = $query->latest()->paginate(20);
        $exhibitions = Exhibition::all();

        return view('admin.checklists.index', compact('checklistItems', 'exhibitions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'due_date_days_before' => 'nullable|integer|min:0',
            'visible_to_user' => 'boolean',
            'visible_to_admin' => 'boolean',
        ]);

        $validated['is_required'] = $request->has('is_required');
        $validated['visible_to_user'] = $request->has('visible_to_user');
        $validated['visible_to_admin'] = $request->has('visible_to_admin');
        $validated['is_active'] = true;

        ChecklistItem::create($validated);
        return redirect()->route('admin.checklists.index')->with('success', 'Checklist item created successfully.');
    }

    public function update(Request $request, $id)
    {
        $item = ChecklistItem::findOrFail($id);
        $validated = $request->validate([
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'due_date_days_before' => 'nullable|integer|min:0',
            'visible_to_user' => 'boolean',
            'visible_to_admin' => 'boolean',
        ]);

        $validated['is_required'] = $request->has('is_required');
        $validated['visible_to_user'] = $request->has('visible_to_user');
        $validated['visible_to_admin'] = $request->has('visible_to_admin');

        $item->update($validated);
        return redirect()->route('admin.checklists.index')->with('success', 'Checklist item updated successfully.');
    }

    public function destroy($id)
    {
        $item = ChecklistItem::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.checklists.index')->with('success', 'Checklist item deleted successfully.');
    }
}
