<x-layouts.app title="Combo">
    <x-page-header title="Quản lý combo" subtitle="Danh sách combo và tạo combo mới qua API." />

    <div id="combos-access-state" class="hidden rounded-lg border px-3 py-2 text-sm"></div>

    <x-page-section title="Tạo combo mới">
        <div id="combo-form-message" class="mb-3 hidden rounded-lg border px-3 py-2 text-sm"></div>
        <form id="combo-form" class="grid gap-4 md:grid-cols-3" action="javascript:void(0)">
            <div>
                <label class="label" for="name">Tên combo</label>
                <input id="name" name="name" class="field" type="text" placeholder="Tên combo">
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="price">Giá</label>
                <input id="price" name="price" class="field" type="number" min="0" placeholder="89000">
                <p data-error-for="price" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="stock">Tồn kho</label>
                <input id="stock" name="stock" class="field" type="number" min="0" placeholder="100">
                <p data-error-for="stock" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div class="md:col-span-3">
                <button id="combo-submit" class="btn-primary" type="submit">Tạo combo</button>
            </div>
        </form>
    </x-page-section>

    <x-page-section title="Danh sách combo">
        <div id="combos-state" class="mb-3 text-sm text-[var(--text-muted)]">Đang tải danh sách combo...</div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--line)] text-sm">
                <thead class="bg-[var(--surface-muted)] text-left text-[var(--text-muted)]">
                    <tr>
                        <th class="px-3 py-2">Tên</th>
                        <th class="px-3 py-2">Giá</th>
                        <th class="px-3 py-2">Tồn kho</th>
                    </tr>
                </thead>
                <tbody id="combos-table-body" class="divide-y divide-[var(--line)]"></tbody>
            </table>
        </div>
    </x-page-section>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const form = document.getElementById('combo-form');
            const formMessage = document.getElementById('combo-form-message');
            const accessState = document.getElementById('combos-access-state');
            const submitBtn = document.getElementById('combo-submit');
            const state = document.getElementById('combos-state');
            const tbody = document.getElementById('combos-table-body');
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
                showAccessState('Bạn đang có quyền quản lý combo.', 'success');
            } else if (user) {
                showAccessState('Bạn không có quyền tạo combo. Chỉ có thể xem danh sách.', 'warning');
            } else {
                showAccessState('Bạn đang xem dữ liệu công khai. Đăng nhập tài khoản admin để tạo combo.', 'info');
            }

            if (!canManage) {
                form.querySelectorAll('input, button').forEach((el) => {
                    el.disabled = true;
                });
                submitBtn.textContent = 'Không có quyền tạo combo';
            }

            const schema = {
                name: [
                    (v) => validators.required(v, 'Tên combo là bắt buộc'),
                    (v) => validators.maxLength(v, 255, 'Tên combo tối đa 255 ký tự'),
                ],
                price: [
                    (v) => validators.number(v, 'Giá phải là số'),
                    (v) => validators.min(v, 0, 'Giá không được âm'),
                ],
                stock: [
                    (v) => validators.number(v, 'Tồn kho phải là số'),
                    (v) => validators.min(v, 0, 'Tồn kho không được âm'),
                ],
            };

            const showMessage = (text, type = 'error') => {
                const base = 'mb-3 rounded-lg border px-3 py-2 text-sm';
                const typeClass = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-red-200 bg-red-50 text-red-700';
                formMessage.className = `${base} ${typeClass}`;
                formMessage.textContent = text;
                formMessage.classList.remove('hidden');
            };

            const loadCombos = async () => {
                state.className = 'mb-3 text-sm text-[var(--text-muted)]';
                state.textContent = 'Đang tải danh sách combo...';

                const result = await window.apiRequest('/api/combos?per_page=50&sort_by=created_at&sort_order=desc');
                if (!result.ok) {
                    state.className = 'mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700';
                    state.textContent = window.firstApiError(result.data);
                    tbody.innerHTML = '';
                    return;
                }

                const items = result.data?.data?.items || [];
                if (!items.length) {
                    state.className = 'mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700';
                    state.textContent = 'Chưa có combo nào.';
                    tbody.innerHTML = '';
                    return;
                }

                state.className = 'mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                state.textContent = `Đã tải ${items.length} combo.`;

                tbody.innerHTML = items.map((item) => `
                    <tr>
                        <td class="px-3 py-2">${item.name ?? '-'}</td>
                        <td class="px-3 py-2">${item.price ?? '-'}</td>
                        <td class="px-3 py-2">${item.stock ?? '-'}</td>
                    </tr>
                `).join('');
            };

            form.addEventListener('submit', async () => {
                if (!canManage) {
                    showMessage('Bạn không có quyền tạo combo.');
                    return;
                }

                clearFieldErrors(form);
                formMessage.classList.add('hidden');

                const payload = {
                    name: form.name.value.trim(),
                    price: form.price.value === '' ? null : Number(form.price.value),
                    stock: form.stock.value === '' ? null : Number(form.stock.value),
                };

                const errors = validateValues(payload, schema);
                if (Object.keys(errors).length) {
                    showFieldErrors(form, errors);
                    showMessage('Vui lòng kiểm tra lại dữ liệu biểu mẫu.');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang tạo...';

                const result = await window.apiRequest('/api/combos', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                if (result.ok) {
                    showMessage(result.data?.message || 'Tạo combo thành công', 'success');
                    form.reset();
                    await loadCombos();
                } else if (result.status === 401) {
                    showMessage('Bạn cần đăng nhập để thực hiện thao tác này.');
                } else if (result.status === 403) {
                    showMessage('Bạn không có quyền tạo combo.');
                } else if (result.status === 422 && result.data?.errors) {
                    const backendErrors = normalizeApiErrors(result.data.errors);
                    showFieldErrors(form, backendErrors);
                    showMessage(window.firstApiError(result.data));
                } else {
                    showMessage(window.firstApiError(result.data));
                }

                submitBtn.disabled = false;
                submitBtn.textContent = 'Tạo combo';
            });

            await loadCombos();
        });
    </script>
@endpush


