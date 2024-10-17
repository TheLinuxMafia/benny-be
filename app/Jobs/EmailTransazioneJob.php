<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\EmailTransCarburanti;
use Illuminate\Support\Facades\Mail;
use Log;

class EmailTransazioneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $datitrans;

    /**
     * Create a new job instance.
     */
    public function __construct($datitrans)
    {
        Log::info('Email Job constructor');
        $this->datitrans = $datitrans;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Email Job');
        $email = new EmailTransCarburanti($this->datitrans);
        Mail::to($this->datitrans['email'])->send($email);
    }
}
