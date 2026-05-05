<?php

namespace App\Jobs;
use App\Models\User;
use App\Services\SendSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSmsToUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Number of times the job should be attempted
    public $tries = 3;

    // Timeout in seconds
    public $timeout = 30;

    // Time between retries in seconds
    public $backoff = 60;

    protected $userId;
    protected $message;
    protected $templateId;

    public function __construct($userId, $message, $templateId = null)
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->templateId = $templateId;
    }

    public function handle()
    {
        try {
            $user = User::find($this->userId);
            if ($user && $user->phone) {
                (new SendSmsService())->sendSMS(
                    $user->phone,
                    env('APP_NAME'),
                    $this->message,
                    $this->templateId
                );
            }
        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('SMS job failed: ' . $exception->getMessage());
    }
}
