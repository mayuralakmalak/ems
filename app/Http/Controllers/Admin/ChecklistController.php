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
        $currentExhibition = $exhibitions->firstWhere('id', $request->exhibition_id);

        return view('admin.checklists.index', compact('checklistItems', 'exhibitions', 'currentExhibition'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'item_type' => 'required|string|in:textbox,textarea,file,multiple_file,checkbox',
            'is_required' => 'nullable|in:0,1,on,true,false',
            'due_date_days_before' => 'nullable|integer|min:0',
            'visible_to_user' => 'nullable|in:0,1,on,true,false',
            'visible_to_admin' => 'nullable|in:0,1,on,true,false',
        ]);

        // If the hidden field wasn't set, fall back to the query param
        if (empty($validated['exhibition_id'])) {
            $validated['exhibition_id'] = $request->query('exhibition_id');
        }

        $validated['is_required'] = $request->boolean('is_required');
        $validated['visible_to_user'] = $request->boolean('visible_to_user', true);
        $validated['visible_to_admin'] = $request->boolean('visible_to_admin', true);
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
            'item_type' => 'required|string|in:textbox,textarea,file,multiple_file,checkbox',
            'is_required' => 'nullable|in:0,1,on,true,false',
            'due_date_days_before' => 'nullable|integer|min:0',
            'visible_to_user' => 'nullable|in:0,1,on,true,false',
            'visible_to_admin' => 'nullable|in:0,1,on,true,false',
        ]);

        if (empty($validated['exhibition_id'])) {
            $validated['exhibition_id'] = $request->query('exhibition_id');
        }

        $validated['is_required'] = $request->boolean('is_required');
        $validated['visible_to_user'] = $request->boolean('visible_to_user', true);
        $validated['visible_to_admin'] = $request->boolean('visible_to_admin', true);

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
