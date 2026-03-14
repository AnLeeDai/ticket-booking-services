<?php

namespace App\Console\Commands;

use App\Services\TicketServices;
use Illuminate\Console\Command;

class CancelExpiredTickets extends Command
{
    protected $signature = 'tickets:cancel-expired';

    protected $description = 'Tự động huỷ vé IS_PENDING quá hạn hold và giải phóng ghế';

    public function handle(TicketServices $ticketServices): int
    {
        $cancelled = $ticketServices->cancelExpiredPendingTickets();

        if ($cancelled > 0) {
            $this->info("Đã huỷ {$cancelled} vé quá hạn thanh toán.");
        } else {
            $this->info('Không có vé nào cần huỷ.');
        }

        return self::SUCCESS;
    }
}
