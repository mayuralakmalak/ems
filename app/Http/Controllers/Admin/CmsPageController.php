<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsPageController extends Controller
{
    public function index()
    {
        $pages = CmsPage::orderBy('title')->get();
        return view('admin.cms-pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.cms-pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:cms_pages,slug',
            'content' => 'nullable|string',
            'show_in_footer' => 'nullable|boolean',
            'show_in_header' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['show_in_footer'] = (bool) ($request->filled('show_in_footer') && $request->show_in_footer);
        $data['show_in_header'] = (bool) ($request->filled('show_in_header') && $request->show_in_header);
        $data['is_active'] = (bool) ($request->filled('is_active') && $request->is_active);

        CmsPage::create($data);

        return redirect()->route('admin.cms-pages.index')->with('success', 'CMS page created successfully.');
    }

    public function edit(CmsPage $cms_page)
    {
        return view('admin.cms-pages.edit', compact('cms_page'));
    }

    public function update(Request $request, CmsPage $cms_page)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:cms_pages,slug,' . $cmsPage->id,
            'content' => 'nullable|string',
            'show_in_footer' => 'nullable|boolean',
            'show_in_header' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['show_in_footer'] = (bool) ($request->filled('show_in_footer') && $request->show_in_footer);
        $data['show_in_header'] = (bool) ($request->filled('show_in_header') && $request->show_in_header);
        $data['is_active'] = (bool) ($request->filled('is_active') && $request->is_active);

        $cms_page->update($data);

        return redirect()->route('admin.cms-pages.index')->with('success', 'CMS page updated successfully.');
    }

    public function destroy(CmsPage $cms_page)
    {
        $cms_page->delete();
        return redirect()->route('admin.cms-pages.index')->with('success', 'CMS page deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'page_ids' => 'required|string',
        ]);

        $ids = json_decode($request->page_ids);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No pages selected for deletion.');
        }

        CmsPage::whereIn('id', $ids)->delete();
        return back()->with('success', count($ids) . ' page(s) deleted successfully.');
    }
}
