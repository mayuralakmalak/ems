<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = DocumentCategory::orderBy('order')->orderBy('name')->get();
        return view('admin.document-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.document-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:document_categories,name',
            'status' => 'required|in:active,inactive',
            'order' => 'nullable|integer|min:0',
        ]);

        DocumentCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
            'order' => $request->order ?? 0,
        ]);

        return redirect()->route('admin.document-categories.index')
            ->with('success', 'Document category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = DocumentCategory::findOrFail($id);
        return view('admin.document-categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = DocumentCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:document_categories,name,' . $id,
            'status' => 'required|in:active,inactive',
            'order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->status,
            'order' => $request->order ?? 0,
        ]);

        return redirect()->route('admin.document-categories.index')
            ->with('success', 'Document category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = DocumentCategory::findOrFail($id);
        
        // Check if category is being used by any documents
        if ($category->documents()->count() > 0) {
            return back()->with('error', 'Cannot delete category. It is being used by ' . $category->documents()->count() . ' document(s).');
        }

        $category->delete();

        return redirect()->route('admin.document-categories.index')
            ->with('success', 'Document category deleted successfully.');
    }
}
