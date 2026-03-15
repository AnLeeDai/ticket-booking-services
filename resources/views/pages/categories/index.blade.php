<x-layouts.app title="Thể loại">
    <x-page-header title="Quản lý thể loại" subtitle="Danh sách và cập nhật thể loại qua API." />

    <div id="categories-access-state" class="hidden rounded-lg border px-3 py-2 text-sm"></div>

    <x-page-section title="Tạo mới / Cập nhật thể loại">
        <div id="category-form-message" class="mb-3 hidden rounded-lg border px-3 py-2 text-sm"></div>

        <form id="category-form" class="grid gap-4 md:grid-cols-2" action="javascript:void(0)">
            <input type="hidden" id="category_id" name="category_id" value="">

            <div>
                <label class="label" for="name">Tên thể loại</label>
                <input id="name" name="name" class="field" type="text" placeholder="Tên thể loại">
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div>
                <label class="label" for="description">Mô tả</label>
                <input id="description" name="description" class="field" type="text" placeholder="Mô tả (tùy chọn)">
                <p data-error-for="description" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button id="category-submit" class="btn-primary" type="submit">Tạo thể loại</button>
                <button id="category-cancel-edit" class="btn-secondary hidden" type="button">Hủy chỉnh sửa</button>
            </div>
        </form>
    </x-page-section>

    <x-page-section title="Danh sách thể loại">
        <div id="categories-state" class="mb-3 text-sm text-[var(--text-muted)]">Đang tải danh mục...</div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--line)] text-sm">
                <thead class="bg-[var(--surface-muted)] text-left text-[var(--text-muted)]">
                    <tr>
                        <th class="px-3 py-2">Tên</th>
                        <th class="px-3 py-2">Slug</th>
                        <th class="px-3 py-2">Mô tả</th>
                        <th class="px-3 py-2">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body" class="divide-y divide-[var(--line)]"></tbody>
            </table>
        </div>
    </x-page-section>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const form = document.getElementById('category-form');
            const formMessage = document.getElementById('category-form-message');
            const accessState = document.getElementById('categories-access-state');
            const submitBtn = document.getElementById('category-submit');
            const cancelEditBtn = document.getElementById('category-cancel-edit');
            const hiddenId = document.getElementById('category_id');
            const state = document.getElementById('categories-state');
            const tbody = document.getElementById('categories-table-body');
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
                showAccessState('Bạn đang có quyền quản lý thể loại.', 'success');
            } else if (user) {
                showAccessState('Bạn không có quyền chỉnh sửa thể loại. Chỉ có thể xem danh sách.', 'warning');
            } else {
                showAccessState('Bạn đang xem dữ liệu công khai. Đăng nhập tài khoản admin để chỉnh sửa.', 'info');
            }

            if (!canManage) {
                form.querySelectorAll('input, button').forEach((el) => {
                    el.disabled = true;
                });
                submitBtn.textContent = 'Không có quyền thao tác';
            }

            const schema = {
                name: [
                    (v) => validators.required(v, 'Tên danh mục là bắt buộc'),
                    (v) => validators.maxLength(v, 255, 'Tên danh mục tối đa 255 ký tự'),
                ],
                description: [
                    (v) => validators.maxLength(v, 1000, 'Mô tả không hợp lệ'),
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

            const setEditMode = (item = null) => {
                if (!item) {
                    hiddenId.value = '';
                    form.name.value = '';
                    form.description.value = '';
                    submitBtn.textContent = canManage ? 'Tạo thể loại' : 'Không có quyền thao tác';
                    cancelEditBtn.classList.add('hidden');
                    return;
                }

                hiddenId.value = item.id;
                form.name.value = item.name ?? '';
                form.description.value = item.description ?? '';
                submitBtn.textContent = 'Cập nhật thể loại';
                cancelEditBtn.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const loadCategories = async () => {
                state.className = 'mb-3 text-sm text-[var(--text-muted)]';
                state.textContent = 'Đang tải danh mục...';

                const result = await window.apiRequest('/api/categories?per_page=50&sort_by=name&sort_order=asc');

                if (!result.ok) {
                    state.className = 'mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700';
                    state.textContent = window.firstApiError(result.data);
                    tbody.innerHTML = '';
                    return;
                }

                const items = result.data?.data?.items || [];

                if (!items.length) {
                    state.className = 'mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700';
                    state.textContent = 'Chưa có danh mục nào.';
                    tbody.innerHTML = '';
                    return;
                }

                state.className = 'mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                state.textContent = `Đã tải ${items.length} danh mục.`;

                tbody.innerHTML = items.map((item) => `
                    <tr>
                        <td class="px-3 py-2">${item.name ?? '-'}</td>
                        <td class="px-3 py-2">${item.slug ?? '-'}</td>
                        <td class="px-3 py-2">${item.description ?? '-'}</td>
                        <td class="px-3 py-2">
                            ${canManage ? `<button type="button" class="text-[var(--brand)]" data-edit-id="${item.id}">Chỉnh sửa</button>` : '-'}
                        </td>
                    </tr>
                `).join('');

                tbody.querySelectorAll('[data-edit-id]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const item = items.find((x) => x.id === btn.dataset.editId);
                        setEditMode(item);
                    });
                });
            };

            cancelEditBtn.addEventListener('click', () => {
                clearFieldErrors(form);
                formMessage.classList.add('hidden');
                setEditMode();
            });

            form.addEventListener('submit', async () => {
                if (!canManage) {
                    showMessage('Bạn không có quyền quản lý thể loại.');
                    return;
                }

                clearFieldErrors(form);
                formMessage.classList.add('hidden');

                const payload = {
                    name: form.name.value.trim(),
                    description: form.description.value.trim() || null,
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

                const result = await window.apiRequest(isEdit ? `/api/categories/${hiddenId.value}` : '/api/categories', {
                    method: isEdit ? 'PUT' : 'POST',
                    body: JSON.stringify(payload),
                });

                if (result.ok) {
                    showMessage(result.data?.message || (isEdit ? 'Cập nhật thành công' : 'Tạo thành công'), 'success');
                    setEditMode();
                    await loadCategories();
                } else if (result.status === 401) {
                    showMessage('Bạn cần đăng nhập để thực hiện thao tác này.');
                } else if (result.status === 403) {
                    showMessage('Bạn không có quyền quản lý thể loại.');
                } else if (result.status === 422 && result.data?.errors) {
                    const backendErrors = normalizeApiErrors(result.data.errors);
                    showFieldErrors(form, backendErrors);
                    showMessage(window.firstApiError(result.data));
                } else {
                    showMessage(window.firstApiError(result.data));
                }

                submitBtn.disabled = false;
                submitBtn.textContent = isEdit ? 'Cập nhật thể loại' : 'Tạo thể loại';
            });

            await loadCategories();
        });
    </script>
@endpush


