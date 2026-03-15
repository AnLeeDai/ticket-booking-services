<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordCodeNotification extends Notification
{
    public function __construct(
        protected string $token
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $broker = config('auth.defaults.passwords', 'users');
        $expire = (int) config("auth.passwords.{$broker}.expire", 60);

        return (new MailMessage)
            ->subject('Yêu cầu đặt lại mật khẩu')
            ->greeting('Xin chào,')
            ->line('Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
            ->line('Vui lòng sử dụng **mã xác nhận** bên dưới để tiếp tục quá trình đặt lại mật khẩu:')
            ->line("🔐 **Mã đặt lại mật khẩu:** {$this->token}")
            ->line("⏱️ Mã này có hiệu lực trong **{$expire} phút** kể từ thời điểm bạn nhận được email.")
            ->line('**Hướng dẫn thực hiện:**')
            ->line('1. Quay lại màn hình đặt lại mật khẩu trên ứng dụng/website.')
            ->line('2. Nhập mã xác nhận ở trên.')
            ->line('3. Tạo mật khẩu mới và hoàn tất quá trình.')
            ->line('Nếu bạn **không thực hiện** yêu cầu này, vui lòng bỏ qua email. Không có thay đổi nào được thực hiện đối với tài khoản của bạn.')
            ->salutation('Trân trọng,');
    }
}
