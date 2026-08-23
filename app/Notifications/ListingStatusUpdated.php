<?php

namespace App\Notifications;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class ListingStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(protected Listing $listing)
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
        $mail = (new MailMessage)
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('**'.$this->listing->title.'** — '.Number::currency((float) $this->listing->price, in: 'BRL'));

        if ($this->listing->status === ListingStatus::Ativo) {
            return $mail
                ->subject('Seu anúncio foi aprovado!')
                ->line('Boas notícias: seu anúncio foi aprovado por um administrador e já está disponível no Marketplace.')
                ->action('Ver anúncio', url('/anuncios/'.$this->listing->slug));
        }

        return $mail
            ->subject('Seu anúncio foi rejeitado')
            ->line('Seu anúncio foi analisado por um administrador e não foi aprovado para publicação.')
            ->action('Ver meus anúncios', url('/meus-anuncios'));
    }
}
