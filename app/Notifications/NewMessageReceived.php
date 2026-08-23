<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class NewMessageReceived extends Notification
{
    use Queueable;

    public function __construct(protected Message $message, protected Conversation $conversation)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $listing = $this->conversation->listing;

        return (new MailMessage)
            ->subject('Nova mensagem sobre "'.$listing->title.'"')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line($this->message->sender->name.' enviou uma nova mensagem sobre o anúncio abaixo:')
            ->line('**'.$listing->title.'** — '.Number::currency((float) $listing->price, in: 'BRL'))
            ->line('Local: '.$listing->city.'/'.$listing->state)
            ->line('Mensagem: "'.$this->message->body.'"')
            ->action('Ver conversa', url('/mensagens/'.$this->conversation->id))
            ->line('Responda diretamente pelo Marketplace para continuar a negociação.');
    }
}
