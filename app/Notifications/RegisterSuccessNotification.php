<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterSuccessNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Dang ky tai khoan thanh cong')
            ->greeting('Xin chao,')
            ->line('Tai khoan cua ban da duoc tao thanh cong.')
            ->line('Ten dang nhap: ' . $notifiable->username)
            ->line('Email: ' . $notifiable->email)
            ->line('Neu ban khong thuc hien dang ky, vui long lien he ho tro ngay.')
            ->salutation('Tran trong,');
    }
}
