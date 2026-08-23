<?php

namespace App\Notifications;

use App\Models\Category;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCategoryRequested extends Notification
{
    use Queueable;

    public function __construct(protected Category $category)
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
        return (new MailMessage)
            ->subject('Nova categoria aguardando aprovação')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Um usuário cadastrou uma categoria nova no Marketplace ADBN e está aguardando aprovação.')
            ->line('Categoria: '.$this->category->name)
            ->action('Revisar no painel', url('/admin/categories'))
            ->line('Nenhuma ação é necessária até que você revise o cadastro.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Nova categoria pendente')
            ->body('"'.$this->category->name.'" está aguardando aprovação.')
            ->icon('heroicon-o-tag')
            ->getDatabaseMessage() + ['url' => '/admin/categories'];
    }
}
