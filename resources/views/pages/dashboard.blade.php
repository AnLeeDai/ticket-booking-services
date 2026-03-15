<x-layouts.app title="Tổng quan">
    <x-page-header title="Tổng quan" subtitle="Tổng hợp nhanh tình hình hệ thống đặt vé.">
        <x-slot:actions>
            <button class="btn-secondary" type="button">Xuất báo cáo</button>
            <button class="btn-primary" type="button">Tạo mới</button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-page-section title="Tổng số phim">
            <p class="text-3xl font-semibold">15</p>
            <p class="caption mt-1">Dữ liệu mẫu</p>
        </x-page-section>
        <x-page-section title="Lượt đặt vé hôm nay">
            <p class="text-3xl font-semibold">124</p>
            <p class="caption mt-1">Dữ liệu mẫu</p>
        </x-page-section>
        <x-page-section title="Rạp đang hoạt động">
            <p class="text-3xl font-semibold">8</p>
            <p class="caption mt-1">Dữ liệu mẫu</p>
        </x-page-section>
        <x-page-section title="Thanh toán đang chờ">
            <p class="text-3xl font-semibold">19</p>
            <p class="caption mt-1">Dữ liệu mẫu</p>
        </x-page-section>
    </div>

    <x-page-section title="Hoạt động gần đây" description="Giao diện mẫu, sẵn sàng kết nối dữ liệu API.">
        <ul class="space-y-3 text-sm text-[var(--text-muted)]">
            <li class="rounded-lg border border-[var(--line)] p-3">Khách hàng vừa đặt vé cho phim mới.</li>
            <li class="rounded-lg border border-[var(--line)] p-3">Quản lý vừa cập nhật lịch chiếu tại rạp.</li>
            <li class="rounded-lg border border-[var(--line)] p-3">Một vé đang chờ đã được hủy.</li>
        </ul>
    </x-page-section>
</x-layouts.app>


