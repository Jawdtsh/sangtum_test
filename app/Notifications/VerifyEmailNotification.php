<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    public function __construct()
    {

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
        $verificationUrl = url('/verify?code=' . $notifiable->email_verification_code);
        return (new MailMessage)
            ->subject('Account Verification')
            ->greeting('Hello ' . $notifiable->username . ',')
            ->line('Thank you for registering with Made Solution. To complete your registration, please verify your email address by clicking the button below.')
            ->action('Verify Account', $verificationUrl)
            ->line('Or you can add this code : '.$notifiable->email_verification_code)
            ->line('to complete the verification.')
            ->line('If you did not create an account, no further action is required.')
            ->line('Thank you for using Made Solution!');
    }



    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
