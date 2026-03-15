<x-layouts.app title="Rạp chiếu">
    <x-page-header title="Quản lý rạp chiếu" subtitle="Danh sách rạp và tạo rạp mới qua API." />

    <div id="cinemas-access-state" class="hidden rounded-lg border px-3 py-2 text-sm"></div>

    <x-page-section title="Tạo rạp mới">
        <div id="cinema-form-message" class="mb-3 hidden rounded-lg border px-3 py-2 text-sm"></div>
        <form id="cinema-create-form" class="grid gap-4 md:grid-cols-2" action="javascript:void(0)">
            <div>
                <label class="label" for="name">Tên rạp</label>
                <input id="name" name="name" class="field" type="text" placeholder="CGV Landmark 81">
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="active">Trạng thái</label>
                <select id="active" name="active" class="field">
                    <option value="IN_ACTIVE">IN_ACTIVE</option>
                    <option value="UN_ACTIVE">UN_ACTIVE</option>
                </select>
                <p data-error-for="active" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div class="md:col-span-2">
                <label class="label" for="location">Địa điểm</label>
                <input id="location" name="location" class="field" type="text" placeholder="Quận/Huyện, Tỉnh/Thành phố">
                <p data-error-for="location" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div class="md:col-span-2">
                <label class="label" for="manager_id">ID quản lý (UUID tùy chọn)</label>
                <input id="manager_id" name="manager_id" class="field" type="text" placeholder="uuid">
                <p data-error-for="manager_id" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div class="md:col-span-2">
                <button id="cinema-submit" class="btn-primary" type="submit">Tạo rạp</button>
            </div>
        </form>
    </x-page-section>

    <x-page-section title="Danh sách rạp">
        <div id="cinemas-state" class="mb-3 text-sm text-[var(--text-muted)]">Đang tải dữ liệu rạp...</div>
        <div id="cinemas-list" class="grid gap-4 lg:grid-cols-2"></div>
    </x-page-section>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const form = document.getElementById('cinema-create-form');
            const formMessage = document.getElementById('cinema-form-message');
            const accessState = document.getElementById('cinemas-access-state');
            const submitBtn = document.getElementById('cinema-submit');
            const state = document.getElementById('cinemas-state');
            const list = document.getElementById('cinemas-list');
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
            const canManage = Boolean(user && window.WebGuard.hasAnyRole(user, ['admin']));

            if (canManage) {
                showAccessState('Bạn đang có quyền tạo rạp.', 'success');
            } else if (user) {
                showAccessState('Bạn không có quyền tạo rạp. Chỉ có thể xem danh sách.', 'warning');
            } else {
                showAccessState('Bạn đang xem dữ liệu công khai. Đăng nhập tài khoản admin để tạo rạp.', 'info');
            }

            if (!canManage) {
                form.querySelectorAll('input, select, button').forEach((el) => {
                    el.disabled = true;
                });
                submitBtn.textContent = 'Không có quyền tạo rạp';
            }

            const showFormMessage = (text, type = 'error') => {
                const base = 'mb-3 rounded-lg border px-3 py-2 text-sm';
                const typeClass = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-red-200 bg-red-50 text-red-700';

                formMessage.className = `${base} ${typeClass}`;
                formMessage.textContent = text;
                formMessage.classList.remove('hidden');
            };

            const loadCinemas = async () => {
                state.className = 'mb-3 text-sm text-[var(--text-muted)]';
                state.textContent = 'Đang tải dữ liệu rạp...';

                const result = await window.apiRequest('/api/cinemas?per_page=20&sort_by=created_at&sort_order=desc');

                if (!result.ok) {
                    state.className = 'mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700';
                    state.textContent = result.data?.message || 'Không thể tải danh sách rạp.';
                    list.innerHTML = '';
                    return;
                }

                const items = result.data?.data?.items || [];

                if (!items.length) {
                    state.className = 'mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700';
                    state.textContent = 'Chưa có rạp nào.';
                    list.innerHTML = '';
                    return;
                }

                state.className = 'mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                state.textContent = `Đã tải ${items.length} rạp.`;

                list.innerHTML = items.map((cinema) => {
                    const active = cinema.active ?? '-';
                    const badge = active === 'IN_ACTIVE'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-amber-100 text-amber-700';

                    return `
                        <article class="rounded-xl border border-[var(--line)] p-4">
                            <h3 class="font-semibold">${cinema.name ?? '-'}</h3>
                            <p class="mt-1 text-sm text-[var(--text-muted)]">${cinema.location ?? '-'}</p>
                            <div class="mt-3 flex items-center gap-2 text-xs">
                                <span class="rounded-full px-2 py-1 ${badge}">${active}</span>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-slate-700">${cinema.code ?? 'Không có mã'}</span>
                            </div>
                        </article>
                    `;
                }).join('');
            };

            const schema = {
                name: [
                    (v) => validators.required(v, 'Tên rạp là bắt buộc'),
                    (v) => validators.maxLength(v, 255, 'Tên rạp tối đa 255 ký tự'),
                ],
                location: [
                    (v) => validators.required(v, 'Địa điểm rạp là bắt buộc'),
                    (v) => validators.maxLength(v, 500, 'Địa chỉ tối đa 500 ký tự'),
                ],
                active: [
                    (v) => validators.enum(v, ['IN_ACTIVE', 'UN_ACTIVE'], 'Trạng thái không hợp lệ'),
                ],
                manager_id: [
                    (v) => validators.uuid(v, 'ID quản lý phải là UUID hợp lệ'),
                ],
            };

            form.addEventListener('submit', async () => {
                if (!canManage) {
                    showFormMessage('Bạn không có quyền tạo rạp.');
                    return;
                }

                clearFieldErrors(form);
                formMessage.classList.add('hidden');

                const payload = {
                    name: form.name.value.trim(),
                    location: form.location.value.trim(),
                    active: form.active.value || null,
                    manager_id: form.manager_id.value.trim() || null,
                };

                const errors = validateValues(payload, schema);
                if (Object.keys(errors).length) {
                    showFieldErrors(form, errors);
                    showFormMessage('Vui lòng kiểm tra lại dữ liệu biểu mẫu.');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang tạo...';

                const result = await window.apiRequest('/api/cinemas', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                if (result.ok) {
                    showFormMessage(result.data?.message || 'Tạo rạp thành công', 'success');
                    form.reset();
                    await loadCinemas();
                } else if (result.status === 401) {
                    showFormMessage('Bạn cần đăng nhập để thực hiện thao tác này.');
                } else if (result.status === 403) {
                    showFormMessage('Bạn không có quyền tạo rạp.');
                } else if (result.status === 422 && result.data?.errors) {
                    const backendErrors = normalizeApiErrors(result.data.errors);
                    showFieldErrors(form, backendErrors);
                    showFormMessage(window.firstApiError(result.data));
                } else {
                    showFormMessage(window.firstApiError(result.data));
                }

                submitBtn.disabled = false;
                submitBtn.textContent = 'Tạo rạp';
            });

            await loadCinemas();
        });
    </script>
@endpush


