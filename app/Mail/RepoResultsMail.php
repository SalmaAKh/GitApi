<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RepoResultsMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $fileName;

    /**
     * Create a new message instance.
     *
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Repo Results Mail')
            ->view('emails.repo_results')
            ->attach(storage_path('app/' . $this->fileName), [
                'as' => 'repositories.xlsx',
            ]);
    }
}
