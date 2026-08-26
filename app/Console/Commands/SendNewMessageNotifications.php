<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Notifications\NewMessageReceived;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendNewMessageNotifications extends Command
{
    protected $signature = 'messages:notify';

    protected $description = 'Envia por e-mail um resumo das mensagens novas de cada conversa desde o último envio';

    public function handle(): void
    {
        $pendingMessages = Message::query()
            ->whereNull('email_notified_at')
            ->with(['conversation', 'sender'])
            ->orderBy('created_at')
            ->get()
            ->groupBy('conversation_id');

        foreach ($pendingMessages as $messages) {
            $conversation = $messages->first()->conversation;

            foreach ($messages->groupBy('sender_id') as $senderMessages) {
                $senderId = $senderMessages->first()->sender_id;

                $recipient = $senderId === $conversation->buyer_id
                    ? $conversation->seller
                    : $conversation->buyer;

                if ($recipient) {
                    Notification::send($recipient, new NewMessageReceived($senderMessages, $conversation));
                }
            }
        }

        Message::query()
            ->whereNull('email_notified_at')
            ->update(['email_notified_at' => now()]);

        $this->info('Notificações de novas mensagens enviadas.');
    }
}
