<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

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
        return ['mail', 'database'];
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

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $listing = $this->conversation->listing;
        $url = '/mensagens/'.$this->conversation->id;

        return FilamentNotification::make()
            ->title('Nova mensagem sobre "'.$listing->title.'"')
            ->body($this->message->sender->name.': '.Str::limit($this->message->body, 80))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->getDatabaseMessage() + ['url' => $url];
    }
}
