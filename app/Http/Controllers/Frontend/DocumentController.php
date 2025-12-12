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
        
        // Get bookings for upload form
        $bookings = Booking::where('user_id', auth()->id())
            ->where('status', '!=', 'cancelled')
            ->with('exhibition')
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
                'category' => 'required|string|in:' . implode(',', $validCategories),
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
                ->findOrFail($request->booking_id);

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

            // For each file, create a document with the selected category
            foreach ($files as $fileIndex => $file) {
                try {
                    if (!$file->isValid()) {
                        $errors[] = 'File ' . ($fileIndex + 1) . ' is not valid.';
                        continue;
                    }

                    $path = $file->store('documents', 'public');
                    
                    // Use original filename without extension as document name
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    $document = Document::create([
                        'booking_id' => $booking->id,
                        'user_id' => auth()->id(),
                        'name' => $fileName,
                        'type' => $category,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'status' => 'pending',
                    ]);
                    
                    $uploadedCount++;
                    
                    // Notify all admins about new document
                    $admins = User::role('Admin')->orWhere('id', 1)->get();
                    foreach ($admins as $admin) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'type' => 'document',
                            'title' => 'New Document Uploaded',
                            'message' => auth()->user()->name . ' has uploaded a new document: ' . $fileName . ' (' . ucfirst($category) . ') for booking #' . $booking->booking_number,
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

            return redirect()->route('documents.index')->with('success', $message);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Document upload error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
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
        $document = Document::where('user_id', auth()->id())->findOrFail($id);
        $bookings = Booking::where('user_id', auth()->id())->get();
        
        // Get active document categories
        $categories = DocumentCategory::active()->ordered()->get();
        
        return view('frontend.documents.edit', compact('document', 'bookings', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $document = Document::where('user_id', auth()->id())->findOrFail($id);

        // Get valid category slugs
        $validCategories = DocumentCategory::active()->pluck('slug')->toArray();

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', $validCategories),
            'files' => 'nullable|array|min:1',
            'files.*' => 'required|file|max:' . self::MAX_FILE_SIZE . '|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'category.required' => 'Please select a category.',
            'category.in' => 'Invalid category selected.',
        ]);

        $category = $request->input('category');
        $files = $request->hasFile('files') ? $request->file('files') : [];
        
        // If files are uploaded, process them
        if (!empty($files) && is_array($files)) {
            $files = array_filter($files, function($file) {
                return $file !== null && $file->isValid();
            });
        }

        // If no files uploaded, just update category for existing document
        if (empty($files)) {
            $document->update([
            'name' => $request->name,
                'type' => $category,
            'status' => 'pending', // Always re-verify after any edit
            ]);

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
                    
                    $document->update([
                        'name' => $fileName ?: $request->name,
                        'type' => $category,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'status' => 'pending',
                    ]);
                    $uploadedCount++;
                } else {
                    // Create new document for additional files
                    Document::create([
                        'booking_id' => $document->booking_id,
                        'user_id' => auth()->id(),
                        'name' => $fileName ?: $request->name,
                        'type' => $category,
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
        
        // Delete file
        if ($document->file_path && \Storage::disk('public')->exists($document->file_path)) {
            \Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
