<x-layouts.auth title="Đăng nhập">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-semibold">Đăng nhập hệ thống</h1>
        <p class="mt-1 text-sm text-[var(--text-muted)]">Sử dụng tài khoản đã cấp để truy cập trang quản trị.</p>
    </div>

    <div id="login-message" class="mb-4 hidden rounded-lg border px-3 py-2 text-sm"></div>

    <form id="login-form" class="space-y-4" action="javascript:void(0)">
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" name="email" class="field" type="email" placeholder="admin@ticketbooking.com" required>
            <p data-error-for="email" class="mt-1 hidden text-xs text-red-600"></p>
        </div>

        <div>
            <label class="label" for="password">Mật khẩu</label>
            <input id="password" name="password" class="field" type="password" placeholder="********" required>
            <p data-error-for="password" class="mt-1 hidden text-xs text-red-600"></p>
        </div>

        <div>
            <label class="label" for="device_name">Tên thiết bị</label>
            <input id="device_name" name="device_name" class="field" type="text" placeholder="Trình duyệt / Postman" value="Web Browser" required>
            <p data-error-for="device_name" class="mt-1 hidden text-xs text-red-600"></p>
        </div>

        <button id="login-submit" class="btn-primary w-full" type="submit">Đăng nhập</button>
    </form>

    <p class="mt-4 text-center text-sm text-[var(--text-muted)]">
        Tài khoản demo: admin@ticketbooking.com / Password@123
    </p>
</x-layouts.auth>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('login-form');
            const messageBox = document.getElementById('login-message');
            const submitBtn = document.getElementById('login-submit');
            const nextPath = new URLSearchParams(window.location.search).get('next') || '/profile';
            const {
                validators,
                validateValues,
                clearFieldErrors,
                showFieldErrors,
                normalizeApiErrors,
            } = window.FormValidation;

            const schema = {
                email: [
                    (v) => validators.required(v, 'Email là bắt buộc'),
                    (v) => validators.email(v, 'Email không hợp lệ'),
                ],
                password: [
                    (v) => validators.required(v, 'Mật khẩu là bắt buộc'),
                ],
                device_name: [
                    (v) => validators.required(v, 'Tên thiết bị là bắt buộc'),
                    (v) => validators.maxLength(v, 100, 'Tên thiết bị tối đa 100 ký tự'),
                ],
            };

            const showMessage = (text, type = 'error') => {
                const base = 'mb-4 rounded-lg border px-3 py-2 text-sm';
                const typeClass = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-red-200 bg-red-50 text-red-700';

                messageBox.className = `${base} ${typeClass}`;
                messageBox.textContent = text;
                messageBox.classList.remove('hidden');
            };

            form.addEventListener('submit', async () => {
                clearFieldErrors(form);
                messageBox.classList.add('hidden');

                const payload = {
                    email: form.email.value.trim(),
                    password: form.password.value,
                    device_name: form.device_name.value.trim(),
                };

                const errors = validateValues(payload, schema);
                if (Object.keys(errors).length) {
                    showFieldErrors(form, errors);
                    showMessage('Vui lòng kiểm tra lại dữ liệu đã nhập.');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang đăng nhập...';

                const result = await window.apiRequest('/api/login', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                if (result.ok && result.data?.data?.access_token) {
                    window.ApiAuth.setToken(result.data.data.access_token);
                    showMessage(result.data.message || 'Đăng nhập thành công', 'success');

                    setTimeout(() => {
                        window.location.href = nextPath;
                    }, 600);

                    return;
                }

                if (result.status === 422 && result.data?.errors) {
                    const backendErrors = normalizeApiErrors(result.data.errors);
                    showFieldErrors(form, backendErrors);
                    showMessage(window.firstApiError(result.data));
                } else {
                    showMessage(window.firstApiError(result.data));
                }

                submitBtn.disabled = false;
                submitBtn.textContent = 'Đăng nhập';
            });
        });
    </script>
@endpush


