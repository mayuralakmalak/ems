<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Booking;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // Maximum upload size in KB (5 MB = 5120 KB)
    const MAX_FILE_SIZE = 5120;
    // Maximum number of documents per booking
    const MAX_DOCUMENTS_PER_BOOKING = 10;

    public function index()
    {
        $documents = Document::where('user_id', auth()->id())
            ->with('booking')
            ->latest()
            ->get();
        return view('frontend.documents.index', compact('documents'));
    }

    public function create()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->get();
        return view('frontend.documents.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'file' => 'required|file|max:' . self::MAX_FILE_SIZE . '|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $booking = Booking::where('user_id', auth()->id())
            ->findOrFail($request->booking_id);

        // Check document limit per booking
        $existingCount = Document::where('booking_id', $booking->id)->count();
        if ($existingCount >= self::MAX_DOCUMENTS_PER_BOOKING) {
            return back()->with('error', 'Maximum ' . self::MAX_DOCUMENTS_PER_BOOKING . ' documents allowed per booking.');
        }

        $path = $request->file('file')->store('documents', 'public');

        Document::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'name' => $request->name,
            'type' => $request->type,
            'file_path' => $path,
            'file_size' => $request->file('file')->getSize(),
            'status' => 'pending',
        ]);

        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully.');
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
            ->findOrFail($id);
        $bookings = Booking::where('user_id', auth()->id())->get();
        return view('frontend.documents.edit', compact('document', 'bookings'));
    }

    public function update(Request $request, string $id)
    {
        $document = Document::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'file' => 'nullable|file|max:' . self::MAX_FILE_SIZE . '|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $updateData = [
            'name' => $request->name,
            'type' => $request->type,
            'status' => 'pending', // Reset to pending when updated
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path && \Storage::disk('public')->exists($document->file_path)) {
                \Storage::disk('public')->delete($document->file_path);
            }
            $updateData['file_path'] = $request->file('file')->store('documents', 'public');
            $updateData['file_size'] = $request->file('file')->getSize();
        }

        $document->update($updateData);

        return redirect()->route('documents.index')->with('success', 'Document updated successfully.');
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
