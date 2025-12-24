<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $status; // 'approved' or 'rejected'
    public $comments; // verification_comments or rejection_reason

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document, string $status, ?string $comments = null)
    {
        $this->document = $document;
        $this->status = $status;
        $this->comments = $comments;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $documentName = $this->document->name;
        $statusText = ucfirst($this->status);
        $subject = "Document \"{$documentName}\" {$statusText}";

        $viewName = $this->status === 'approved' 
            ? 'emails.document-approved' 
            : 'emails.document-rejected';

        return $this->subject($subject)
                    ->view($viewName)
                    ->with([
                        'document' => $this->document,
                        'status' => $this->status,
                        'comments' => $this->comments,
                    ]);
    }
}

