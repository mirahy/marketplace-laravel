<?php

namespace App\Notifications;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CategoryStatusUpdated extends Notification
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
        $mail = (new MailMessage)
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Categoria: **'.$this->category->name.'**');

        if ($this->category->status === CategoryStatus::Aprovado) {
            return $mail
                ->subject('Sua categoria foi aprovada!')
                ->line('Boas notícias: a categoria que você cadastrou foi aprovada por um administrador e já pode ser usada nos anúncios.')
                ->action('Criar anúncio', url('/anuncios/novo'));
        }

        return $mail
            ->subject('Sua categoria não foi aprovada')
            ->line('A categoria que você cadastrou foi analisada por um administrador e não foi aprovada.')
            ->action('Ver anúncios', url('/anuncios'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $isApproved = $this->category->status === CategoryStatus::Aprovado;

        return FilamentNotification::make()
            ->title($isApproved ? 'Categoria aprovada' : 'Categoria rejeitada')
            ->body('"'.$this->category->name.'" foi '.($isApproved ? 'aprovada.' : 'rejeitada.'))
            ->icon($isApproved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
            ->getDatabaseMessage() + ['url' => '/anuncios/novo'];
    }
}
