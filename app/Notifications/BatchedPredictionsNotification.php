<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BatchedPredictionsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $predictions;

    /**
     * Create a new notification instance.
     */
    public function __construct(Collection $predictions)
    {
        $this->predictions = $predictions;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->predictions->count();
        Log::info("Preparing batched email with {$count} predictions");

        return (new MailMessage)
            ->subject("🎯 {$count} New Prediction" . ($count > 1 ? 's' : '') . " Available!")
            ->view('emails.batched-predictions', ['predictions' => $this->predictions]);
    }
}
