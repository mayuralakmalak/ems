<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of active document categories (read-only for exhibitors).
     */
    public function index()
    {
        // Only show active categories to exhibitors (read-only)
        $categories = DocumentCategory::where('status', 'active')
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        return view('frontend.document-categories.index', compact('categories'));
    }
}
