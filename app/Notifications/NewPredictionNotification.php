<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Prediction;
use Illuminate\Support\Facades\Log;

class NewPredictionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $prediction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Prediction $prediction)
    {
        $this->prediction = $prediction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // Add 'database' if we had in-app notifications
        // Note: Ensure MAIL_ environment variables are set.
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::info("Preparing email for prediction: " . $this->prediction->home_team);

        return (new MailMessage)
            ->subject('🎯 New Tip: ' . $this->prediction->home_team . ' vs ' . $this->prediction->away_team)
            ->view('emails.new-prediction', ['prediction' => $this->prediction]);
    }
}
