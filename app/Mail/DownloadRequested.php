<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Email ke perusahaan: ada pengunjung yang meminta mengunduh satu atau beberapa file. */
class DownloadRequested extends Mailable
{
    use Queueable;

    public function __construct(
        public array $req,
        public array $files,
        public string $owner,
        public string $reviewUrl,
        public string $panelUrl,
    ) {}

    public function envelope(): Envelope
    {
        $first = $this->files[0]['title'] ?? 'file';
        $more = count($this->files) > 1 ? ' (+' . (count($this->files) - 1) . ' file)' : '';

        return new Envelope(subject: '[Permintaan Download] ' . $this->req['requester_name'] . ' - ' . $first . $more);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.download-request');
    }
}