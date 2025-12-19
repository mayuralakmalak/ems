<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Models\Exhibition;
use App\Models\Notification;
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

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by exhibition
        if ($request->has('exhibition_id') && $request->exhibition_id) {
            $query->whereHas('booking', function($q) use ($request) {
                $q->where('exhibition_id', $request->exhibition_id);
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

        $users = User::orderBy('name')->get(['id', 'name', 'company_name']);
        $exhibitions = Exhibition::orderBy('name')->get(['id', 'name']);
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin.documents.partials.table', compact('documents'))->render(),
                'pagination' => view('admin.documents.partials.pagination', compact('documents'))->render()
            ]);
        }
        
        return view('admin.documents.index', compact(
            'documents',
            'totalExhibitors',
            'docsPendingVerification',
            'docsExpiringSoon',
            'missingDocs',
            'users',
            'exhibitions'
        ));
    }
    
    public function show($id)
    {
        $document = Document::with(['user', 'booking.exhibition', 'requiredDocument'])->findOrFail($id);
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('admin.documents.show', compact('document'))->render()
            ]);
        }
        
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
        
        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Approved',
            'message' => 'Your document "' . $document->name . '" has been approved.',
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Document approved successfully.',
                'document' => $document->fresh(['user', 'booking.exhibition', 'requiredDocument'])
            ]);
        }
        
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
        
        // Notify exhibitor
        Notification::create([
            'user_id' => $document->user_id,
            'type' => 'document',
            'title' => 'Document Rejected',
            'message' => 'Your document "' . $document->name . '" has been rejected. Reason: ' . $request->rejection_reason,
            'notifiable_type' => Document::class,
            'notifiable_id' => $document->id,
        ]);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Document rejected successfully.',
                'document' => $document->fresh(['user', 'booking.exhibition', 'requiredDocument'])
            ]);
        }
        
        return back()->with('success', 'Document rejected.');
    }
    
    public function bulkApprove(Request $request)
    {
        // Handle JSON input from AJAX
        $documentIds = $request->input('document_ids', []);
        
        // If it's a JSON string, decode it
        if (is_string($documentIds)) {
            $decoded = json_decode($documentIds, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $documentIds = $decoded;
            }
        }
        
        // Ensure it's an array
        if (!is_array($documentIds)) {
            $documentIds = [];
        }
        
        $request->merge(['document_ids' => $documentIds]);
        
        $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'required|exists:documents,id',
        ]);
        
        Document::whereIn('id', $documentIds)
            ->update(['status' => 'approved']);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($documentIds) . ' document(s) approved successfully.'
            ]);
        }
        
        return back()->with('success', 'Documents approved successfully.');
    }
}

