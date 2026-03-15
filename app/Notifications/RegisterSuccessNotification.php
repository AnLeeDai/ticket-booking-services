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
            ->subject('Đăng ký tài khoản thành công')
            ->greeting('Xin chào,')
            ->line('Tài khoản của bạn đã được tạo thành công.')
            ->line('Tên đăng nhập: '.$notifiable->user_name)
            ->line('Email: '.$notifiable->email)
            ->line('Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.')
            ->salutation('Trân trọng,');
    }
}
