<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['user', 'booking.exhibition']);
        
        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $documents = $query->latest()->paginate(20);
        
        // Calculate statistics
        $totalExhibitors = User::whereHas('bookings')->distinct()->count();
        $docsPendingVerification = Document::where('status', 'pending')->count();
        $docsExpiringSoon = Document::where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addMonths(3))
            ->count();
        $missingDocs = Document::where('status', 'rejected')->count();
        
        return view('admin.documents.index', compact(
            'documents',
            'totalExhibitors',
            'docsPendingVerification',
            'docsExpiringSoon',
            'missingDocs'
        ));
    }
    
    public function show($id)
    {
        $document = Document::with(['user', 'booking.exhibition'])->findOrFail($id);
        return view('admin.documents.show', compact('document'));
    }
    
    public function approve(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $request->validate([
            'verification_comments' => 'nullable|string|max:1000',
        ]);
        
        $document->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);
        
        return back()->with('success', 'Document approved successfully.');
    }
    
    public function reject(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        return back()->with('success', 'Document rejected.');
    }
    
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);
        
        Document::whereIn('id', $request->document_ids)
            ->update(['status' => 'approved']);
        
        return back()->with('success', 'Documents approved successfully.');
    }
}

