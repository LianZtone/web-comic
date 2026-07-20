<?php

namespace App\Notifications;

use App\Support\AdminTwoFactor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminTwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
    ) {
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
        return (new MailMessage())
            ->subject('Kode Verifikasi Admin Velmics')
            ->greeting('Verifikasi login admin')
            ->line('Ada percobaan login ke panel admin menggunakan akun ini.')
            ->line('Masukkan kode berikut untuk menyelesaikan login admin:')
            ->line($this->code)
            ->line('Kode berlaku selama '.AdminTwoFactor::EXPIRES_AFTER_MINUTES.' menit.')
            ->line('Jika ini bukan kamu, abaikan email ini dan segera ganti password admin.');
    }
}
