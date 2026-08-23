<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    use Queueable;

    public function __construct(protected User $user)
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
        return (new MailMessage)
            ->subject('Novo cadastro aguardando aprovação')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Um novo usuário se cadastrou no Marketplace ADBN e está aguardando aprovação.')
            ->line('Nome: '.$this->user->name)
            ->line('E-mail: '.$this->user->email)
            ->action('Revisar no painel', url('/admin/users'))
            ->line('Nenhuma ação é necessária até que você revise o cadastro.');
    }
}
