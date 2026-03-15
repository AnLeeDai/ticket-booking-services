<x-layouts.app title="Suất chiếu">
    <x-page-header title="Quản lý suất chiếu" subtitle="Danh sách và cập nhật lịch chiếu phục vụ đặt vé." />

    <div id="showtimes-access-state" class="hidden rounded-lg border px-3 py-2 text-sm"></div>

    <x-page-section title="Tạo mới / Cập nhật suất chiếu" description="Khi tạo mới, starts_at cần lớn hơn thời điểm hiện tại; cập nhật cho phép điều chỉnh lịch.">
        <div id="showtime-form-message" class="mb-3 hidden rounded-lg border px-3 py-2 text-sm"></div>

        <form id="showtime-form" class="grid gap-4 md:grid-cols-2" action="javascript:void(0)">
            <input type="hidden" id="showtime_id" name="showtime_id" value="">

            <div>
                <label class="label" for="cinema_id">Rạp chiếu</label>
                <select id="cinema_id" name="cinema_id" class="field"></select>
                <p data-error-for="cinema_id" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="movie_id">Phim</label>
                <select id="movie_id" name="movie_id" class="field"></select>
                <p data-error-for="movie_id" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="starts_at">Bắt đầu lúc</label>
                <input id="starts_at" name="starts_at" class="field" type="datetime-local">
                <p data-error-for="starts_at" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="ends_at">Kết thúc lúc</label>
                <input id="ends_at" name="ends_at" class="field" type="datetime-local">
                <p data-error-for="ends_at" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="screen_type">Loại phòng chiếu</label>
                <select id="screen_type" name="screen_type" class="field">
                    <option value="2D">2D</option>
                    <option value="3D">3D</option>
                </select>
                <p data-error-for="screen_type" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button id="showtime-submit" class="btn-primary" type="submit">Tạo suất chiếu</button>
                <button id="showtime-cancel-edit" class="btn-secondary hidden" type="button">Hủy chỉnh sửa</button>
            </div>
        </form>
    </x-page-section>

    <x-page-section title="Danh sách suất chiếu">
        <div id="showtimes-state" class="mb-3 text-sm text-[var(--text-muted)]">Đang tải suất chiếu...</div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--line)] text-sm">
                <thead class="bg-[var(--surface-muted)] text-left text-[var(--text-muted)]">
                    <tr>
                        <th class="px-3 py-2">Phim</th>
                        <th class="px-3 py-2">Rạp</th>
                        <th class="px-3 py-2">Bắt đầu</th>
                        <th class="px-3 py-2">Kết thúc</th>
                        <th class="px-3 py-2">Loại phòng</th>
                        <th class="px-3 py-2">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="showtimes-table-body" class="divide-y divide-[var(--line)]"></tbody>
            </table>
        </div>
    </x-page-section>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const form = document.getElementById('showtime-form');
            const messageBox = document.getElementById('showtime-form-message');
            const accessState = document.getElementById('showtimes-access-state');
            const submitBtn = document.getElementById('showtime-submit');
            const cancelBtn = document.getElementById('showtime-cancel-edit');
            const hiddenId = document.getElementById('showtime_id');
            const movieSelect = document.getElementById('movie_id');
            const cinemaSelect = document.getElementById('cinema_id');
            const state = document.getElementById('showtimes-state');
            const tbody = document.getElementById('showtimes-table-body');
            const {
                validators,
                validateValues,
                clearFieldErrors,
                showFieldErrors,
                normalizeApiErrors,
            } = window.FormValidation;

            const showAccessState = (text, type = 'info') => {
                const base = 'rounded-lg border px-3 py-2 text-sm';
                const typeClass = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : type === 'warning'
                        ? 'border-amber-200 bg-amber-50 text-amber-700'
                        : 'border-sky-200 bg-sky-50 text-sky-700';
                accessState.className = `${base} ${typeClass}`;
                accessState.textContent = text;
                accessState.classList.remove('hidden');
            };

            const user = await window.WebGuard.getCurrentUser();
            const canManage = Boolean(user && window.WebGuard.hasAnyRole(user, ['admin', 'manager']));

            if (canManage) {
                showAccessState('Bạn đang có quyền quản lý suất chiếu.', 'success');
            } else if (user) {
                showAccessState('Bạn không có quyền cập nhật suất chiếu. Chỉ có thể xem danh sách.', 'warning');
            } else {
                showAccessState('Bạn đang xem dữ liệu công khai. Đăng nhập tài khoản admin/manager để chỉnh sửa.', 'info');
            }

            if (!canManage) {
                form.querySelectorAll('input, select, button').forEach((el) => {
                    el.disabled = true;
                });
                submitBtn.textContent = 'Không có quyền thao tác';
            }

            let movies = [];
            let cinemas = [];

            const schema = {
                cinema_id: [
                    (v) => validators.required(v, 'Rạp chiếu là bắt buộc'),
                    (v) => validators.uuid(v, 'ID rạp chiếu không hợp lệ'),
                ],
                movie_id: [
                    (v) => validators.required(v, 'Phim là bắt buộc'),
                    (v) => validators.uuid(v, 'ID phim không hợp lệ'),
                ],
                starts_at: [
                    (v) => validators.required(v, 'Thời điểm bắt đầu là bắt buộc'),
                    (v) => validators.date(v, 'Thời điểm bắt đầu không hợp lệ'),
                ],
                ends_at: [
                    (v) => validators.required(v, 'Thời điểm kết thúc là bắt buộc'),
                    (v) => validators.date(v, 'Thời điểm kết thúc không hợp lệ'),
                    (v, values) => {
                        if (!v || !values.starts_at) return null;
                        return new Date(v).getTime() > new Date(values.starts_at).getTime()
                            ? null
                            : 'Thời điểm kết thúc phải sau thời điểm bắt đầu';
                    },
                ],
                screen_type: [
                    (v) => validators.required(v, 'Loại phòng chiếu là bắt buộc'),
                    (v) => validators.enum(v, ['2D', '3D'], 'Loại phòng chiếu không hợp lệ'),
                ],
            };

            const toDateTimeLocal = (value) => {
                if (!value) return '';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '';
                const pad = (n) => String(n).padStart(2, '0');
                return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
            };

            const showMessage = (text, type = 'error') => {
                const base = 'mb-3 rounded-lg border px-3 py-2 text-sm';
                const typeClass = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-red-200 bg-red-50 text-red-700';
                messageBox.className = `${base} ${typeClass}`;
                messageBox.textContent = text;
                messageBox.classList.remove('hidden');
            };

            const setEditMode = (item = null) => {
                if (!item) {
                    hiddenId.value = '';
                    form.reset();
                    submitBtn.textContent = canManage ? 'Tạo suất chiếu' : 'Không có quyền thao tác';
                    cancelBtn.classList.add('hidden');
                    return;
                }

                hiddenId.value = item.showtime_id;
                form.movie_id.value = item.movie_id || '';
                form.cinema_id.value = item.cinema_id || '';
                form.starts_at.value = toDateTimeLocal(item.starts_at);
                form.ends_at.value = toDateTimeLocal(item.ends_at);
                form.screen_type.value = item.screen_type || '2D';
                submitBtn.textContent = 'Cập nhật suất chiếu';
                cancelBtn.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const loadReferenceData = async () => {
                const [moviesResult, cinemasResult] = await Promise.all([
                    window.apiRequest('/api/movies?per_page=100&sort_by=created_at&sort_order=desc'),
                    window.apiRequest('/api/cinemas?per_page=100&sort_by=created_at&sort_order=desc'),
                ]);

                movies = moviesResult.ok ? (moviesResult.data?.data?.items || []) : [];
                cinemas = cinemasResult.ok ? (cinemasResult.data?.data?.items || []) : [];

                movieSelect.innerHTML = '<option value="">-- Chọn phim --</option>' + movies
                    .map((item) => `<option value="${item.movie_id}">${item.title ?? item.name ?? '-'}</option>`)
                    .join('');

                cinemaSelect.innerHTML = '<option value="">-- Chọn rạp chiếu --</option>' + cinemas
                    .map((item) => `<option value="${item.cinema_id}">${item.name ?? '-'}</option>`)
                    .join('');
            };

            const loadShowtimes = async () => {
                state.className = 'mb-3 text-sm text-[var(--text-muted)]';
                state.textContent = 'Đang tải suất chiếu...';

                const result = await window.apiRequest('/api/showtimes?per_page=50&sort_by=starts_at&sort_order=asc');
                if (!result.ok) {
                    state.className = 'mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700';
                    state.textContent = window.firstApiError(result.data);
                    tbody.innerHTML = '';
                    return;
                }

                const items = result.data?.data?.items || [];
                if (!items.length) {
                    state.className = 'mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700';
                    state.textContent = 'Chưa có suất chiếu nào.';
                    tbody.innerHTML = '';
                    return;
                }

                state.className = 'mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                state.textContent = `Đã tải ${items.length} suất chiếu.`;

                tbody.innerHTML = items.map((item) => `
                    <tr>
                        <td class="px-3 py-2">${item.movie?.title ?? item.movie?.name ?? '-'}</td>
                        <td class="px-3 py-2">${item.cinema?.name ?? '-'}</td>
                        <td class="px-3 py-2">${item.starts_at ?? '-'}</td>
                        <td class="px-3 py-2">${item.ends_at ?? '-'}</td>
                        <td class="px-3 py-2">${item.screen_type ?? '-'}</td>
                        <td class="px-3 py-2">
                            ${canManage ? `<button type="button" class="text-[var(--brand)]" data-edit-id="${item.showtime_id}">Chỉnh sửa</button>` : '-'}
                        </td>
                    </tr>
                `).join('');

                tbody.querySelectorAll('[data-edit-id]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const item = items.find((x) => x.showtime_id === btn.dataset.editId);
                        setEditMode(item);
                    });
                });
            };

            cancelBtn.addEventListener('click', () => {
                clearFieldErrors(form);
                messageBox.classList.add('hidden');
                setEditMode();
            });

            form.addEventListener('submit', async () => {
                if (!canManage) {
                    showMessage('Bạn không có quyền quản lý suất chiếu.');
                    return;
                }

                clearFieldErrors(form);
                messageBox.classList.add('hidden');

                const payload = {
                    cinema_id: form.cinema_id.value,
                    movie_id: form.movie_id.value,
                    starts_at: form.starts_at.value,
                    ends_at: form.ends_at.value,
                    screen_type: form.screen_type.value,
                };

                const errors = validateValues(payload, schema);
                if (Object.keys(errors).length) {
                    showFieldErrors(form, errors);
                    showMessage('Vui lòng kiểm tra lại dữ liệu biểu mẫu.');
                    return;
                }

                const isEdit = Boolean(hiddenId.value);
                submitBtn.disabled = true;
                submitBtn.textContent = isEdit ? 'Đang cập nhật...' : 'Đang tạo...';

                const result = await window.apiRequest(isEdit ? `/api/showtimes/${hiddenId.value}` : '/api/showtimes', {
                    method: isEdit ? 'PUT' : 'POST',
                    body: JSON.stringify(payload),
                });

                if (result.ok) {
                    showMessage(result.data?.message || (isEdit ? 'Cập nhật thành công' : 'Tạo thành công'), 'success');
                    setEditMode();
                    await loadShowtimes();
                } else if (result.status === 401) {
                    showMessage('Bạn cần đăng nhập để thực hiện thao tác này.');
                } else if (result.status === 403) {
                    showMessage('Bạn không có quyền quản lý suất chiếu.');
                } else if (result.status === 422 && result.data?.errors) {
                    const backendErrors = normalizeApiErrors(result.data.errors);
                    showFieldErrors(form, backendErrors);
                    showMessage(window.firstApiError(result.data));
                } else {
                    showMessage(window.firstApiError(result.data));
                }

                submitBtn.disabled = false;
                submitBtn.textContent = isEdit ? 'Cập nhật suất chiếu' : 'Tạo suất chiếu';
            });

            await Promise.all([loadReferenceData(), loadShowtimes()]);
        });
    </script>
@endpush


