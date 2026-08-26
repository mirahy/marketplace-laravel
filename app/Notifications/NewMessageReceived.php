<?php

namespace App\Notifications;

use App\Models\Conversation;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class NewMessageReceived extends Notification
{
    use Queueable;

    /**
     * @param  Collection<int, \App\Models\Message>  $messages
     */
    public function __construct(protected Collection $messages, protected Conversation $conversation)
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
        $sender = $this->messages->last()->sender;

        $mail = (new MailMessage)
            ->subject('Nova mensagem sobre "'.$listing->title.'"')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Você tem uma nova mensagem de '.$sender->name.'.')
            ->line('**'.$listing->title.'** — '.Number::currency((float) $listing->price, in: 'BRL'))
            ->line('Local: '.$listing->city.'/'.$listing->state);

        foreach ($this->messages as $message) {
            $mail->line('Mensagem: "'.$message->body.'"');
        }

        return $mail
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
        $lastMessage = $this->messages->last();

        return FilamentNotification::make()
            ->title('Nova mensagem sobre "'.$listing->title.'"')
            ->body($lastMessage->sender->name.': '.Str::limit($lastMessage->body, 80))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->getDatabaseMessage() + ['url' => $url];
    }
}
