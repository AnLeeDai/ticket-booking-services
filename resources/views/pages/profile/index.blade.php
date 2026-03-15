<x-layouts.app title="Hồ sơ">
    <x-page-header title="Hồ sơ cá nhân" subtitle="Cập nhật thông tin tài khoản của bạn.">
        <x-slot:actions>
            <button id="logout-button" class="btn-secondary" type="button">Đăng xuất</button>
        </x-slot:actions>
    </x-page-header>

    <div id="profile-message" class="hidden rounded-lg border px-3 py-2 text-sm"></div>

    <x-page-section title="Thông tin hồ sơ" description="Dữ liệu được tải từ API hồ sơ người dùng.">
        <form id="profile-form" class="grid gap-4 md:grid-cols-2" action="javascript:void(0)">
            <div>
                <label class="label" for="full_name">Họ và tên</label>
                <input id="full_name" name="full_name" class="field" type="text" value="">
                <p data-error-for="full_name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="user_name">Tên đăng nhập</label>
                <input id="user_name" name="user_name" class="field" type="text" value="">
                <p data-error-for="user_name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="phone">Số điện thoại</label>
                <input id="phone" name="phone" class="field" type="text" value="">
                <p data-error-for="phone" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="dob">Ngày sinh</label>
                <input id="dob" name="dob" class="field" type="date" value="">
                <p data-error-for="dob" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="md:col-span-2">
                <label class="label" for="address">Địa chỉ</label>
                <input id="address" name="address" class="field" type="text" value="">
                <p data-error-for="address" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="md:col-span-2">
                <label class="label" for="avatar_url">Đường dẫn ảnh đại diện</label>
                <input id="avatar_url" name="avatar_url" class="field" type="url" value="">
                <p data-error-for="avatar_url" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button id="profile-submit" class="btn-primary" type="submit">Lưu hồ sơ</button>
            </div>
        </form>
    </x-page-section>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const form = document.getElementById('profile-form');
            const messageBox = document.getElementById('profile-message');
            const logoutButton = document.getElementById('logout-button');
            const profileSubmit = document.getElementById('profile-submit');
            const {
                validators,
                validateValues,
                clearFieldErrors,
                showFieldErrors,
                normalizeApiErrors,
            } = window.FormValidation;
            const schema = {
                full_name: [(v) => validators.maxLength(v, 255, 'Họ tên tối đa 255 ký tự')],
                user_name: [(v) => validators.maxLength(v, 50, 'Tên đăng nhập tối đa 50 ký tự')],
                phone: [(v) => validators.maxLength(v, 20, 'Số điện thoại tối đa 20 ký tự')],
                dob: [(v) => validators.date(v, 'Ngày sinh không hợp lệ')],
                address: [(v) => validators.maxLength(v, 255, 'Địa chỉ tối đa 255 ký tự')],
                avatar_url: [(v) => validators.url(v, 'Đường dẫn ảnh đại diện không hợp lệ')],
            };

            const showMessage = (text, type = 'error') => {
                const base = 'rounded-lg border px-3 py-2 text-sm';
                const typeClass = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-amber-200 bg-amber-50 text-amber-700';

                messageBox.className = `${base} ${typeClass}`;
                messageBox.textContent = text;
                messageBox.classList.remove('hidden');
            };

            const authResult = await window.WebGuard.requireAuth();
            if (!authResult.ok) {
                return;
            }

            const user = authResult.user || {};
            document.getElementById('full_name').value = user.full_name || '';
            document.getElementById('user_name').value = user.user_name || '';
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('dob').value = user.dob || '';
            document.getElementById('address').value = user.address || '';
            document.getElementById('avatar_url').value = user.avatar_url || '';

            showMessage('Đã tải thông tin hồ sơ thành công.', 'success');

            form.addEventListener('submit', async () => {
                clearFieldErrors(form);

                const payload = {
                    full_name: form.full_name.value.trim(),
                    user_name: form.user_name.value.trim(),
                    phone: form.phone.value.trim(),
                    dob: form.dob.value,
                    address: form.address.value.trim(),
                    avatar_url: form.avatar_url.value.trim(),
                };

                ['phone', 'dob', 'address', 'avatar_url'].forEach((field) => {
                    if (payload[field] === '') {
                        payload['clear_' + field] = true;
                        delete payload[field];
                    }
                });

                const errors = validateValues(payload, schema);
                if (Object.keys(errors).length) {
                    showFieldErrors(form, errors);
                    showMessage('Vui lòng kiểm tra lại thông tin.', 'error');
                    return;
                }

                profileSubmit.disabled = true;
                profileSubmit.textContent = 'Đang lưu...';

                const updateResult = await window.apiRequest('/api/users/profile', {
                    method: 'PUT',
                    body: JSON.stringify(payload),
                });

                if (updateResult.ok) {
                    showMessage(updateResult.data?.message || 'Cập nhật thông tin thành công', 'success');
                } else if (updateResult.status === 422 && updateResult.data?.errors) {
                    const backendErrors = normalizeApiErrors(updateResult.data.errors);
                    showFieldErrors(form, backendErrors);
                    showMessage(window.firstApiError(updateResult.data), 'error');
                } else {
                    showMessage(window.firstApiError(updateResult.data), 'error');
                }

                profileSubmit.disabled = false;
                profileSubmit.textContent = 'Lưu hồ sơ';
            });

            logoutButton.addEventListener('click', async () => {
                logoutButton.disabled = true;
                logoutButton.textContent = 'Đang đăng xuất...';

                const logoutResult = await window.apiRequest('/api/auth/logout', {
                    method: 'POST',
                });

                window.ApiAuth.clearToken();

                if (logoutResult.ok) {
                    window.location.href = '/login';
                    return;
                }

                showMessage(logoutResult.data?.message || 'Đã xóa token cục bộ.');
                logoutButton.disabled = false;
                logoutButton.textContent = 'Đăng xuất';
            });
        });
    </script>
@endpush


