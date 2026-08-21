<?php

namespace App\Notifications;

use App\Models\PartnerInformationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PartnerRequestCreatedNotification extends Notification
{
    use Queueable;

    protected $infoRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(PartnerInformationRequest $infoRequest)
    {
        $this->infoRequest = $infoRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = route('finance.partner-requests.show', $this->infoRequest->id);

        return (new MailMessage)
            ->subject('New Partner Information Request')
            ->line('A new information request has been submitted by ' . $this->infoRequest->funder->name . '.')
            ->line('Subject: ' . $this->infoRequest->subject)
            ->line('Type: ' . ucfirst(str_replace('_', ' ', $this->infoRequest->request_type)))
            ->line('Priority: ' . ucfirst($this->infoRequest->priority))
            ->action('View Request', $url)
            ->line('Please review and respond to this request at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'request_id'   => $this->infoRequest->id,
            'funder_id'    => $this->infoRequest->funder_id,
            'funder_name'  => $this->infoRequest->funder->name,
            'subject'      => $this->infoRequest->subject,
            'request_type' => $this->infoRequest->request_type,
            'priority'     => $this->infoRequest->priority,
        ];
    }
}
