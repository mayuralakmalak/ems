<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // Maximum upload size in KB (5 MB = 5120 KB)
    const MAX_FILE_SIZE = 5120;
    // Maximum number of documents per booking
    const MAX_DOCUMENTS_PER_BOOKING = 10;

    public function index(Request $request)
    {
        $query = Document::where('user_id', auth()->id())->with('booking');
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        $documents = $query->latest()->get();
        
        // Get bookings for upload form with required documents and uploaded documents
        $bookings = Booking::where('user_id', auth()->id())
            ->where('status', '!=', 'cancelled')
            ->with(['exhibition.requiredDocuments', 'documents' => function($query) {
                $query->whereNotNull('required_document_id');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get active document categories
        $categories = DocumentCategory::active()->ordered()->get();
        
        return view('frontend.documents.index', compact('documents', 'bookings', 'categories'));
    }

    public function create()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->where('status', '!=', 'cancelled')
            ->with('exhibition')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get active document categories
        $categories = DocumentCategory::active()->ordered()->get();
        
        return view('frontend.documents.create', compact('bookings', 'categories'));
    }

    public function store(Request $request)
    {
        try {
            // Get valid category slugs
            $validCategories = DocumentCategory::active()->pluck('slug')->toArray();
            
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'category' => 'nullable|string|in:' . implode(',', $validCategories),
                'required_document_id' => 'nullable|exists:exhibition_required_documents,id',
                'files' => 'required|array|min:1',
                'files.*' => 'required|file|max:' . self::MAX_FILE_SIZE . '|mimes:pdf,doc,docx,jpg,jpeg,png',
            ], [
                'category.required' => 'Please select a category.',
                'category.in' => 'Invalid category selected.',
                'files.required' => 'Please select at least one file to upload.',
                'files.array' => 'Invalid file format.',
                'files.min' => 'Please select at least one file to upload.',
                'files.*.required' => 'One or more files are missing.',
                'files.*.file' => 'One or more selected items are not valid files.',
                'files.*.max' => 'One or more files exceed the maximum size of ' . (self::MAX_FILE_SIZE / 1024) . ' MB.',
                'files.*.mimes' => 'One or more files have invalid format. Allowed formats: PDF, DOC, DOCX, JPG, JPEG, PNG.',
            ]);

            $booking = Booking::where('user_id', auth()->id())
                ->with('exhibition.requiredDocuments')
                ->findOrFail($request->booking_id);

            // If required_document_id is provided, validate it belongs to the booking's exhibition
            $requiredDocumentId = $request->input('required_document_id');
            if ($requiredDocumentId) {
                $requiredDoc = $booking->exhibition->requiredDocuments->firstWhere('id', $requiredDocumentId);
                if (!$requiredDoc) {
                    return back()->withInput()->with('error', 'Invalid required document selected.');
                }
                
                // Validate file type based on required document type
                $requiredDocType = $requiredDoc->document_type;
                $files = $request->file('files');
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $mimeType = $file->getMimeType();
                        $extension = strtolower($file->getClientOriginalExtension());
                        
                        if ($requiredDocType === 'image' && !in_array($extension, ['jpg', 'jpeg', 'png'])) {
                            return back()->withInput()->with('error', 'This document requires an image file (JPG, JPEG, PNG).');
                        }
                        if ($requiredDocType === 'pdf' && $extension !== 'pdf') {
                            return back()->withInput()->with('error', 'This document requires a PDF file.');
                        }
                        if ($requiredDocType === 'both' && !in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
                            return back()->withInput()->with('error', 'This document requires an image (JPG, JPEG, PNG) or PDF file.');
                        }
                    }
                }
            } else {
                // If no required_document_id, category is required
                if (empty($request->input('category'))) {
                    return back()->withInput()->with('error', 'Please select either a document category or a required document.');
                }
            }

            // Get files array
            $files = $request->file('files');
            $category = $request->input('category');
            
            // Check if files array is empty
            if (empty($files) || !is_array($files)) {
                return back()->withInput()->with('error', 'No files were uploaded. Please select files and try again.');
            }

            // Filter out any null values
            $files = array_filter($files, function($file) {
                return $file !== null && $file->isValid();
            });

            if (empty($files)) {
                return back()->withInput()->with('error', 'No valid files were uploaded. Please check your files and try again.');
            }

            // Check document limit per booking
            $existingCount = Document::where('booking_id', $booking->id)->count();
            $filesCount = count($files);
            
            if ($existingCount + $filesCount > self::MAX_DOCUMENTS_PER_BOOKING) {
                return back()->withInput()->with('error', 'Maximum ' . self::MAX_DOCUMENTS_PER_BOOKING . ' documents allowed per booking. You are trying to upload ' . $filesCount . ' files, but only ' . (self::MAX_DOCUMENTS_PER_BOOKING - $existingCount) . ' slots are available.');
            }

            $uploadedCount = 0;
            $errors = [];

            // For each file, create or update a document with the selected category
            foreach ($files as $fileIndex => $file) {
                try {
                    if (!$file->isValid()) {
                        $errors[] = 'File ' . ($fileIndex + 1) . ' is not valid.';
                        continue;
                    }

                    $path = $file->store('documents', 'public');
                    
                    // Use original filename without extension as document name
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    // If this is a required document, check if there's an existing document to update
                    $isUpdate = false;
                    if ($requiredDocumentId && $fileIndex === 0) {
                        // Find existing document for this required_document_id and booking
                        $existingDocument = Document::where('booking_id', $booking->id)
                            ->where('required_document_id', $requiredDocumentId)
                            ->where('user_id', auth()->id())
                            ->latest() // Get the most recent one
                            ->first();
                        
                        if ($existingDocument) {
                            $isUpdate = true;
                            // Delete old file if it exists and is different
                            if ($existingDocument->file_path && \Storage::disk('public')->exists($existingDocument->file_path) && $existingDocument->file_path !== $path) {
                                \Storage::disk('public')->delete($existingDocument->file_path);
                            }
                            
                            // Update existing document (reset status to pending when re-uploading)
                            $existingDocument->update([
                                'name' => $requiredDoc->document_name,
                                'type' => 'required_document',
                                'file_path' => $path,
                                'file_size' => $file->getSize(),
                                'status' => 'pending', // Reset to pending when re-uploading
                                'rejection_reason' => null, // Clear rejection reason
                            ]);
                            
                            $document = $existingDocument;
                        } else {
                            // Create new document if none exists
                            $document = Document::create([
                                'booking_id' => $booking->id,
                                'user_id' => auth()->id(),
                                'required_document_id' => $requiredDocumentId,
                                'name' => $requiredDoc->document_name,
                                'type' => 'required_document',
                                'file_path' => $path,
                                'file_size' => $file->getSize(),
                                'status' => 'pending',
                            ]);
                        }
                    } else {
                        // Regular document (not required) - always create new
                        $document = Document::create([
                            'booking_id' => $booking->id,
                            'user_id' => auth()->id(),
                            'required_document_id' => $requiredDocumentId,
                            'name' => $requiredDocumentId ? $requiredDoc->document_name : $fileName,
                            'type' => $requiredDocumentId ? 'required_document' : $category,
                            'file_path' => $path,
                            'file_size' => $file->getSize(),
                            'status' => 'pending',
                        ]);
                    }
                    
                    $uploadedCount++;
                    
                    // Notify all admins about new/updated document
                    $admins = User::role('Admin')->orWhere('id', 1)->get();
                    foreach ($admins as $admin) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'type' => 'document',
                            'title' => $isUpdate ? 'Document Re-uploaded' : 'New Document Uploaded',
                            'message' => auth()->user()->name . ' has ' . ($isUpdate ? 're-uploaded' : 'uploaded') . ' a document: ' . ($requiredDocumentId ? $requiredDoc->document_name : $fileName) . ' for booking #' . $booking->booking_number,
                            'notifiable_type' => Document::class,
                            'notifiable_id' => $document->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Failed to upload file ' . ($fileIndex + 1) . ': ' . $e->getMessage();
                }
            }

            if ($uploadedCount === 0) {
                $errorMessage = 'No files were uploaded. ';
                if (!empty($errors)) {
                    $errorMessage .= implode(' ', $errors);
                }
                return back()->withInput()->with('error', $errorMessage);
            }

            $message = $uploadedCount === 1 
                ? 'Document uploaded successfully.' 
                : $uploadedCount . ' documents uploaded successfully.';

            if (!empty($errors)) {
                $message .= ' However, some files failed: ' . implode(' ', $errors);
            }

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'uploaded_count' => $uploadedCount
                ]);
            }
            
            return redirect()->route('documents.index')->with('success', $message);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => implode(' ', $e->errors()['files'] ?? ['Validation failed']),
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Document upload error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while uploading documents: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()->with('error', 'An error occurred while uploading documents: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $document = Document::where('user_id', auth()->id())
            ->with('booking')
            ->findOrFail($id);
        return view('frontend.documents.show', compact('document'));
    }

    public function edit(string $id)
    {
        $document = Document::where('user_id', auth()->id())
            ->with(['booking.exhibition.requiredDocuments', 'requiredDocument'])
            ->findOrFail($id);
        
        // Prevent editing if document is approved
        if (!$document->canBeEdited()) {
            return redirect()->route('documents.index')
                ->with('error', 'You cannot edit an approved document. Please contact admin if you need to make changes.');
        }
        
        $bookings = Booking::where('user_id', auth()->id())->get();
        
        // Get active document categories
        $categories = DocumentCategory::active()->ordered()->get();
        
        return view('frontend.documents.edit', compact('document', 'bookings', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $document = Document::where('user_id', auth()->id())
            ->with(['booking.exhibition.requiredDocuments', 'requiredDocument'])
            ->findOrFail($id);

        // Prevent editing if document is approved
        if (!$document->canBeEdited()) {
            return redirect()->route('documents.index')
                ->with('error', 'You cannot edit an approved document. Please contact admin if you need to make changes.');
        }

        // Get valid category slugs
        $validCategories = DocumentCategory::active()->pluck('slug')->toArray();

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|in:' . implode(',', $validCategories),
            'required_document_id' => 'nullable|exists:exhibition_required_documents,id',
            'files' => 'nullable|array|min:1',
            'files.*' => 'required|file|max:' . self::MAX_FILE_SIZE . '|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'category.in' => 'Invalid category selected.',
        ]);

        // If it's a required document, validate file type
        $requiredDocumentId = $request->input('required_document_id') ?? $document->required_document_id;
        if ($requiredDocumentId) {
            $requiredDoc = $document->booking->exhibition->requiredDocuments->firstWhere('id', $requiredDocumentId);
            if ($requiredDoc) {
                $files = $request->hasFile('files') ? $request->file('files') : [];
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $extension = strtolower($file->getClientOriginalExtension());
                        $requiredDocType = $requiredDoc->document_type;
                        
                        if ($requiredDocType === 'image' && !in_array($extension, ['jpg', 'jpeg', 'png'])) {
                            return back()->withInput()->with('error', 'This document requires an image file (JPG, JPEG, PNG).');
                        }
                        if ($requiredDocType === 'pdf' && $extension !== 'pdf') {
                            return back()->withInput()->with('error', 'This document requires a PDF file.');
                        }
                        if ($requiredDocType === 'both' && !in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
                            return back()->withInput()->with('error', 'This document requires an image (JPG, JPEG, PNG) or PDF file.');
                        }
                    }
                }
            }
        } else {
            // If not a required document, category is required
            if (empty($request->input('category'))) {
                return back()->withInput()->with('error', 'Please select a document category.');
            }
        }

        $category = $request->input('category');
        $files = $request->hasFile('files') ? $request->file('files') : [];
        
        // If files are uploaded, process them
        if (!empty($files) && is_array($files)) {
            $files = array_filter($files, function($file) {
                return $file !== null && $file->isValid();
            });
        }

        // If no files uploaded, just update name/category for existing document
        if (empty($files)) {
            $updateData = [
                'name' => $request->name,
                'status' => 'pending', // Always re-verify after any edit
            ];
            
            if ($requiredDocumentId) {
                $updateData['required_document_id'] = $requiredDocumentId;
                $updateData['type'] = 'required_document';
            } else {
                $updateData['type'] = $category;
            }
            
            $document->update($updateData);

            return redirect()->route('documents.index')->with('success', 'Document updated successfully.');
        }

        // If files are uploaded, update existing document and create new ones for additional files
        $uploadedCount = 0;
        $errors = [];

        foreach ($files as $fileIndex => $file) {
            try {
                if (!$file->isValid()) {
                    $errors[] = 'File ' . ($fileIndex + 1) . ' is not valid.';
                    continue;
                }

                $path = $file->store('documents', 'public');
                $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                if ($fileIndex === 0) {
                    // Update existing document
                    // Delete old file if it exists and is different
                    if ($document->file_path && \Storage::disk('public')->exists($document->file_path) && $document->file_path !== $path) {
                \Storage::disk('public')->delete($document->file_path);
            }
                    
                    $updateData = [
                        'name' => $requiredDocumentId ? ($document->requiredDocument->document_name ?? $fileName) : ($fileName ?: $request->name),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'status' => 'pending',
                    ];
                    
                    if ($requiredDocumentId) {
                        $updateData['required_document_id'] = $requiredDocumentId;
                        $updateData['type'] = 'required_document';
                    } else {
                        $updateData['type'] = $category;
                    }
                    
                    $document->update($updateData);
                    $uploadedCount++;
                } else {
                    // Create new document for additional files
                    Document::create([
                        'booking_id' => $document->booking_id,
                        'user_id' => auth()->id(),
                        'required_document_id' => $requiredDocumentId,
                        'name' => $requiredDocumentId ? ($document->requiredDocument->document_name ?? $fileName) : ($fileName ?: $request->name),
                        'type' => $requiredDocumentId ? 'required_document' : $category,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'status' => 'pending',
                    ]);
                    $uploadedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = 'Failed to upload file ' . ($fileIndex + 1) . ': ' . $e->getMessage();
        }
        }

        if ($uploadedCount === 0 && !empty($errors)) {
            return back()->withInput()->with('error', 'Failed to update documents: ' . implode(' ', $errors));
        }

        $message = $uploadedCount === 1 
            ? 'Document updated successfully.' 
            : $uploadedCount . ' documents updated successfully.';

        if (!empty($errors)) {
            $message .= ' However, some files failed: ' . implode(' ', $errors);
        }

        return redirect()->route('documents.index')->with('success', $message);
    }

    public function destroy(string $id)
    {
        $document = Document::where('user_id', auth()->id())->findOrFail($id);
        
        // Prevent deletion if document is approved
        if (!$document->canBeEdited()) {
            return back()->with('error', 'You cannot delete an approved document. Please contact admin if you need to remove it.');
        }
        
        // Delete file
        if ($document->file_path && \Storage::disk('public')->exists($document->file_path)) {
            \Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    public function requiredDocuments($bookingId)
    {
        $booking = Booking::where('user_id', auth()->id())
            ->with(['exhibition.requiredDocuments', 'documents' => function($query) {
                $query->whereNotNull('required_document_id')
                      ->orderBy('created_at', 'desc'); // Get latest documents first
            }])
            ->findOrFail($bookingId);

        return view('frontend.documents.required', compact('booking'));
    }
}
