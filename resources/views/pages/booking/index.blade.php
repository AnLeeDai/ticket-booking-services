<x-layouts.app title="Đặt vé">
    <x-page-header title="Đặt vé" subtitle="Chọn phim -> suất chiếu -> ghế -> đặt vé trực tiếp qua API." />

    <div id="booking-message" class="hidden rounded-lg border px-3 py-2 text-sm"></div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-page-section title="Bước 1: Chọn phim và suất chiếu" class="xl:col-span-2">
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <label class="label" for="booking_movie_id">Phim</label>
                    <select id="booking_movie_id" class="field"></select>
                </div>
                <div>
                    <label class="label" for="booking_showtime_id">Suất chiếu</label>
                    <select id="booking_showtime_id" class="field"></select>
                </div>
                <div>
                    <label class="label" for="booking_payment_method">Phương thức thanh toán</label>
                    <select id="booking_payment_method" class="field">
                        <option value="TRANSFER">TRANSFER</option>
                        <option value="CARD">CARD</option>
                        <option value="CASH">CASH</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="booking_combo_id">Combo (tùy chọn)</label>
                    <select id="booking_combo_id" class="field"></select>
                </div>
                <div>
                    <label class="label" for="booking_combo_qty">Số lượng combo</label>
                    <input id="booking_combo_qty" class="field" type="number" min="1" max="10" value="1">
                </div>
                <div class="flex items-end">
                    <p id="booking-auth-state" class="caption">Đang kiểm tra trạng thái đăng nhập...</p>
                </div>
            </div>
        </x-page-section>

        <x-page-section title="Bước 2: Tóm tắt thanh toán">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-[var(--text-muted)]">Ghế</dt><dd id="summary-seat">-</dd></div>
                <div class="flex justify-between"><dt class="text-[var(--text-muted)]">Giá vé</dt><dd id="summary-ticket-price">-</dd></div>
                <div class="flex justify-between"><dt class="text-[var(--text-muted)]">Combo</dt><dd id="summary-combo">-</dd></div>
                <div class="flex justify-between border-t border-[var(--line)] pt-2 font-semibold"><dt>Tổng cộng</dt><dd id="summary-total">-</dd></div>
            </dl>
            <button id="booking-submit" class="btn-primary mt-4 w-full" type="button">Đặt vé</button>
        </x-page-section>
    </div>

    <x-page-section title="Bước 3: Chọn ghế">
        <div id="seats-state" class="mb-3 text-sm text-[var(--text-muted)]">Chọn phim và suất chiếu để tải ghế.</div>
        <div id="seats-grid" class="grid grid-cols-6 gap-2 text-center text-xs sm:grid-cols-10 lg:grid-cols-12"></div>
    </x-page-section>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const messageBox = document.getElementById('booking-message');
            const authState = document.getElementById('booking-auth-state');
            const movieSelect = document.getElementById('booking_movie_id');
            const showtimeSelect = document.getElementById('booking_showtime_id');
            const paymentSelect = document.getElementById('booking_payment_method');
            const comboSelect = document.getElementById('booking_combo_id');
            const comboQtyInput = document.getElementById('booking_combo_qty');
            const submitBtn = document.getElementById('booking-submit');
            const seatsState = document.getElementById('seats-state');
            const seatsGrid = document.getElementById('seats-grid');

            const summarySeat = document.getElementById('summary-seat');
            const summaryTicketPrice = document.getElementById('summary-ticket-price');
            const summaryCombo = document.getElementById('summary-combo');
            const summaryTotal = document.getElementById('summary-total');

            let movies = [];
            let showtimes = [];
            let seats = [];
            let combos = [];
            let selectedSeat = null;

            const showMessage = (text, type = 'error') => {
                const base = 'rounded-lg border px-3 py-2 text-sm';
                const typeClass = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : type === 'info'
                        ? 'border-sky-200 bg-sky-50 text-sky-700'
                        : 'border-red-200 bg-red-50 text-red-700';
                messageBox.className = `${base} ${typeClass}`;
                messageBox.textContent = text;
                messageBox.classList.remove('hidden');
            };

            const clearMessage = () => {
                messageBox.classList.add('hidden');
            };

            const formatMoney = (value) => {
                if (value === null || value === undefined || Number.isNaN(Number(value))) return '-';
                return Number(value).toLocaleString('vi-VN');
            };

            const updateSummary = () => {
                const comboId = comboSelect.value;
                const comboQty = Number(comboQtyInput.value || 1);
                const combo = combos.find((c) => c.combo_id === comboId);

                const ticketPrice = selectedSeat ? Number(selectedSeat.price || 0) : 0;
                const comboTotal = combo ? Number(combo.price || 0) * comboQty : 0;
                const total = ticketPrice + comboTotal;

                summarySeat.textContent = selectedSeat ? selectedSeat.seat_code : '-';
                summaryTicketPrice.textContent = selectedSeat ? formatMoney(ticketPrice) : '-';
                summaryCombo.textContent = combo ? `${combo.name} x${comboQty}` : '-';
                summaryTotal.textContent = selectedSeat ? formatMoney(total) : '-';
            };

            const populateMovies = async () => {
                const result = await window.apiRequest('/api/movies?per_page=100&sort_by=created_at&sort_order=desc');
                if (!result.ok) {
                    showMessage(window.firstApiError(result.data));
                    movieSelect.innerHTML = '<option value="">Không tải được danh sách phim</option>';
                    return;
                }

                movies = (result.data?.data?.items || []).filter((m) => m.status === 'IN_ACTIVE');
                movieSelect.innerHTML = '<option value="">-- Chọn phim --</option>' + movies
                    .map((m) => `<option value="${m.movie_id}">${m.title ?? m.name ?? '-'}</option>`)
                    .join('');
            };

            const populateCombos = async () => {
                const result = await window.apiRequest('/api/combos?per_page=100&sort_by=created_at&sort_order=desc');
                if (!result.ok) {
                    comboSelect.innerHTML = '<option value="">Không có combo</option>';
                    return;
                }

                combos = result.data?.data?.items || [];
                comboSelect.innerHTML = '<option value="">-- Không chọn combo --</option>' + combos
                    .map((c) => `<option value="${c.combo_id}">${c.name} (${formatMoney(c.price)})</option>`)
                    .join('');
            };

            const loadShowtimesByMovie = async (movieId) => {
                showtimeSelect.innerHTML = '<option value="">-- Đang tải suất chiếu --</option>';
                seatsGrid.innerHTML = '';
                seatsState.textContent = 'Đang tải suất chiếu...';
                selectedSeat = null;
                updateSummary();

                if (!movieId) {
                    showtimeSelect.innerHTML = '<option value="">-- Chọn suất chiếu --</option>';
                    seatsState.textContent = 'Chọn phim để tải suất chiếu.';
                    return;
                }

                const result = await window.apiRequest(`/api/showtimes?movie_id=${movieId}&sort_by=starts_at&sort_order=asc&per_page=100`);
                if (!result.ok) {
                    showtimeSelect.innerHTML = '<option value="">Không tải được suất chiếu</option>';
                    seatsState.textContent = window.firstApiError(result.data);
                    return;
                }

                const now = Date.now();
                showtimes = (result.data?.data?.items || []).filter((st) => new Date(st.starts_at).getTime() > now);

                if (!showtimes.length) {
                    showtimeSelect.innerHTML = '<option value="">Không có suất chiếu sắp tới</option>';
                    seatsState.textContent = 'Không có suất chiếu phù hợp.';
                    return;
                }

                showtimeSelect.innerHTML = '<option value="">-- Chọn suất chiếu --</option>' + showtimes
                    .map((st) => `<option value="${st.showtime_id}">${st.starts_at} - ${st.screen_type} - ${st.cinema?.name ?? ''}</option>`)
                    .join('');

                seatsState.textContent = 'Chọn suất chiếu để tải ghế.';
            };

            const loadSeatsByShowtime = async (showtimeId) => {
                seatsGrid.innerHTML = '';
                selectedSeat = null;
                updateSummary();

                if (!showtimeId) {
                    seatsState.textContent = 'Chọn suất chiếu để tải ghế.';
                    return;
                }

                seatsState.textContent = 'Đang tải danh sách ghế...';

                const result = await window.apiRequest(`/api/seats/showtime/${showtimeId}?active=IN_ACTIVE&sort_by=seat_code&sort_order=asc&per_page=200`);
                if (!result.ok) {
                    seatsState.textContent = window.firstApiError(result.data);
                    return;
                }

                seats = result.data?.data?.items || [];
                if (!seats.length) {
                    seatsState.textContent = 'Không còn ghế trống cho suất chiếu này.';
                    return;
                }

                seatsState.textContent = `Có ${seats.length} ghế khả dụng. Chọn 1 ghế để đặt vé.`;

                seatsGrid.innerHTML = seats.map((seat) => `
                    <button
                        type="button"
                        class="booking-seat rounded-md border border-[var(--line)] p-2 hover:border-[var(--brand)] hover:text-[var(--brand)]"
                        data-seat-id="${seat.seat_id}"
                    >
                        ${seat.seat_code}
                    </button>
                `).join('');

                seatsGrid.querySelectorAll('.booking-seat').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        seatsGrid.querySelectorAll('.booking-seat').forEach((x) => {
                            x.classList.remove('bg-[var(--brand)]', 'text-white', 'border-[var(--brand)]');
                        });
                        btn.classList.add('bg-[var(--brand)]', 'text-white', 'border-[var(--brand)]');
                        selectedSeat = seats.find((s) => s.seat_id === btn.dataset.seatId) || null;
                        updateSummary();
                    });
                });
            };

            movieSelect.addEventListener('change', async () => {
                clearMessage();
                await loadShowtimesByMovie(movieSelect.value);
            });

            showtimeSelect.addEventListener('change', async () => {
                clearMessage();
                await loadSeatsByShowtime(showtimeSelect.value);
            });

            comboSelect.addEventListener('change', updateSummary);
            comboQtyInput.addEventListener('input', updateSummary);

            submitBtn.addEventListener('click', async () => {
                clearMessage();

                const token = window.ApiAuth.getToken();
                if (!token) {
                    showMessage('Bạn cần đăng nhập trước khi đặt vé.', 'info');
                    return;
                }

                if (!movieSelect.value || !showtimeSelect.value || !selectedSeat) {
                    showMessage('Vui lòng chọn phim, suất chiếu và ghế trước khi đặt vé.');
                    return;
                }

                const payload = {
                    showtime_id: showtimeSelect.value,
                    seat_id: selectedSeat.seat_id,
                    payment_method: paymentSelect.value,
                };

                const selectedComboId = comboSelect.value;
                const comboQty = Number(comboQtyInput.value || 1);
                if (selectedComboId) {
                    payload.combos = [{
                        combo_id: selectedComboId,
                        qty: comboQty,
                    }];
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang đặt vé...';

                const result = await window.apiRequest('/api/tickets/book', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                if (result.ok) {
                    showMessage(result.data?.message || 'Đặt vé thành công', 'success');
                    await loadSeatsByShowtime(showtimeSelect.value);
                } else {
                    showMessage(window.firstApiError(result.data));
                }

                submitBtn.disabled = false;
                submitBtn.textContent = 'Đặt vé';
            });

            const currentUser = await window.WebGuard.getCurrentUser();
            authState.textContent = currentUser
                ? 'Đã đăng nhập. Bạn có thể đặt vé ngay.'
                : 'Chưa đăng nhập. Bạn vẫn xem được lịch chiếu, nhưng cần đăng nhập để đặt vé.';

            await Promise.all([populateMovies(), populateCombos()]);
            showtimeSelect.innerHTML = '<option value="">-- Chọn suất chiếu --</option>';
            movieSelect.value = '';
            updateSummary();
        });
    </script>
@endpush


