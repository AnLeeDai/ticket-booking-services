<x-layouts.app title="Phim">
    <x-page-header title="Quản lý phim" subtitle="Danh sách phim và tạo phim mới qua API." />

    <div id="movies-access-state" class="hidden rounded-lg border px-3 py-2 text-sm"></div>

    <x-page-section title="Tạo phim mới">
        <div id="movie-form-message" class="mb-3 hidden rounded-lg border px-3 py-2 text-sm"></div>

        <form id="movie-create-form" class="grid gap-4 md:grid-cols-2" action="javascript:void(0)">
            <div>
                <label class="label" for="title">Tiêu đề</label>
                <input id="title" name="title" class="field" type="text" placeholder="Tiêu đề phim">
                <p data-error-for="title" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="name">Tên phim</label>
                <input id="name" name="name" class="field" type="text" placeholder="Tên phim">
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="language">Ngôn ngữ</label>
                <input id="language" name="language" class="field" type="text" placeholder="Tiếng Việt">
                <p data-error-for="language" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="status">Trạng thái</label>
                <select id="status" name="status" class="field">
                    <option value="IN_ACTIVE">IN_ACTIVE</option>
                    <option value="IS_PENDING">IS_PENDING</option>
                    <option value="UN_ACTIVE">UN_ACTIVE</option>
                </select>
                <p data-error-for="status" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="duration">Thời lượng (phút)</label>
                <input id="duration" name="duration" class="field" type="number" min="1" placeholder="120">
                <p data-error-for="duration" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="age">Độ tuổi</label>
                <input id="age" name="age" class="field" type="number" min="0" max="255" placeholder="13">
                <p data-error-for="age" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="release_date">Ngày khởi chiếu</label>
                <input id="release_date" name="release_date" class="field" type="date">
                <p data-error-for="release_date" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="end_date">Ngày kết thúc (tùy chọn)</label>
                <input id="end_date" name="end_date" class="field" type="date">
                <p data-error-for="end_date" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="thumb_url">Đường dẫn ảnh thumb</label>
                <input id="thumb_url" name="thumb_url" class="field" type="text" placeholder="https://...">
                <p data-error-for="thumb_url" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <label class="label" for="trailer_url">Đường dẫn trailer</label>
                <input id="trailer_url" name="trailer_url" class="field" type="text" placeholder="https://...">
                <p data-error-for="trailer_url" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="md:col-span-2">
                <label class="label" for="category_ids">Thể loại (chọn nhiều)</label>
                <select id="category_ids" name="category_ids" class="field" multiple size="5"></select>
                <p class="caption mt-1">Giữ Ctrl/Cmd để chọn nhiều mục.</p>
                <p data-error-for="category_ids" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="md:col-span-2">
                <label class="label" for="genre_id">Thể loại chính (tùy chọn)</label>
                <select id="genre_id" name="genre_id" class="field"></select>
                <p data-error-for="genre_id" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="md:col-span-2">
                <button id="movie-submit" class="btn-primary" type="submit">Tạo phim</button>
            </div>
        </form>
    </x-page-section>

    <x-page-section title="Danh sách phim">
        <div id="movies-state" class="mb-3 text-sm text-[var(--text-muted)]">Đang tải dữ liệu phim...</div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--line)] text-sm">
                <thead class="bg-[var(--surface-muted)] text-left text-[var(--text-muted)]">
                    <tr>
                        <th class="px-3 py-2">Mã</th>
                        <th class="px-3 py-2">Tiêu đề</th>
                        <th class="px-3 py-2">Thời lượng</th>
                        <th class="px-3 py-2">Trạng thái</th>
                        <th class="px-3 py-2">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="movies-table-body" class="divide-y divide-[var(--line)]"></tbody>
            </table>
        </div>
    </x-page-section>
</x-layouts.app>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const form = document.getElementById('movie-create-form');
            const formMessage = document.getElementById('movie-form-message');
            const accessState = document.getElementById('movies-access-state');
            const submitBtn = document.getElementById('movie-submit');
            const categorySelect = document.getElementById('category_ids');
            const genreSelect = document.getElementById('genre_id');
            const state = document.getElementById('movies-state');
            const tbody = document.getElementById('movies-table-body');
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
                showAccessState('Bạn đang có quyền quản trị phim.', 'success');
            } else if (user) {
                showAccessState('Bạn không có quyền tạo phim. Chỉ có thể xem danh sách.', 'warning');
            } else {
                showAccessState('Bạn đang xem dữ liệu công khai. Đăng nhập tài khoản admin để tạo phim.', 'info');
            }

            if (!canManage) {
                form.querySelectorAll('input, select, button').forEach((el) => {
                    el.disabled = true;
                });
                submitBtn.textContent = 'Không có quyền tạo phim';
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

            const loadMovies = async () => {
                state.className = 'mb-3 text-sm text-[var(--text-muted)]';
                state.textContent = 'Đang tải dữ liệu phim...';

                const result = await window.apiRequest('/api/movies?per_page=20&sort_by=created_at&sort_order=desc');

                if (!result.ok) {
                    state.className = 'mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700';
                    state.textContent = result.data?.message || 'Không thể tải danh sách phim.';
                    tbody.innerHTML = '';
                    return;
                }

                const items = result.data?.data?.items || [];

                if (!items.length) {
                    state.className = 'mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700';
                    state.textContent = 'Chưa có phim nào.';
                    tbody.innerHTML = '';
                    return;
                }

                state.className = 'mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                state.textContent = `Đã tải ${items.length} phim.`;

                tbody.innerHTML = items.map((movie) => `
                    <tr>
                        <td class="px-3 py-2">${movie.code ?? '-'}</td>
                        <td class="px-3 py-2">${movie.title ?? movie.name ?? '-'}</td>
                        <td class="px-3 py-2">${movie.duration ?? '-'} phút</td>
                        <td class="px-3 py-2">${movie.status ?? '-'}</td>
                        <td class="px-3 py-2"><span class="text-[var(--brand)]">Xem</span></td>
                    </tr>
                `).join('');
            };

            const loadCategories = async () => {
                const result = await window.apiRequest('/api/categories?per_page=100&sort_by=name&sort_order=asc');
                if (!result.ok) {
                    showFormMessage('Không thể tải thể loại để tạo phim.');
                    return;
                }

                const items = result.data?.data?.items || [];

                categorySelect.innerHTML = items.map((c) => `<option value="${c.id}">${c.name}</option>`).join('');
                genreSelect.innerHTML = `<option value="">-- Tùy chọn --</option>` +
                    items.map((c) => `<option value="${c.id}">${c.name}</option>`).join('');
            };

            const schema = {
                title: [
                    (v) => validators.required(v, 'Tiêu đề là bắt buộc'),
                    (v) => validators.maxLength(v, 255, 'Tiêu đề tối đa 255 ký tự'),
                ],
                name: [
                    (v) => validators.required(v, 'Tên phim là bắt buộc'),
                    (v) => validators.maxLength(v, 255, 'Tên phim tối đa 255 ký tự'),
                ],
                language: [
                    (v) => validators.required(v, 'Ngôn ngữ là bắt buộc'),
                    (v) => validators.maxLength(v, 100, 'Ngôn ngữ tối đa 100 ký tự'),
                ],
                status: [
                    (v) => validators.required(v, 'Trạng thái là bắt buộc'),
                    (v) => validators.enum(v, ['IN_ACTIVE', 'IS_PENDING', 'UN_ACTIVE'], 'Trạng thái không hợp lệ'),
                ],
                duration: [
                    (v) => validators.required(v, 'Thời lượng là bắt buộc'),
                    (v) => validators.number(v, 'Thời lượng phải là số'),
                    (v) => validators.min(v, 1, 'Thời lượng tối thiểu là 1'),
                ],
                age: [
                    (v) => validators.required(v, 'Độ tuổi là bắt buộc'),
                    (v) => validators.number(v, 'Độ tuổi phải là số'),
                    (v) => validators.min(v, 0, 'Độ tuổi tối thiểu là 0'),
                    (v) => validators.max(v, 255, 'Độ tuổi tối đa là 255'),
                ],
                release_date: [
                    (v) => validators.required(v, 'Ngày khởi chiếu là bắt buộc'),
                    (v) => validators.date(v, 'Ngày khởi chiếu không hợp lệ'),
                ],
                end_date: [
                    (v) => validators.date(v, 'Ngày kết thúc không hợp lệ'),
                ],
                thumb_url: [
                    (v) => validators.required(v, 'Đường dẫn ảnh thumb là bắt buộc'),
                    (v) => validators.maxLength(v, 500, 'Đường dẫn ảnh thumb tối đa 500 ký tự'),
                ],
                trailer_url: [
                    (v) => validators.required(v, 'Đường dẫn trailer là bắt buộc'),
                    (v) => validators.maxLength(v, 500, 'Đường dẫn trailer tối đa 500 ký tự'),
                ],
                category_ids: [
                    (v) => validators.required(v, 'Cần chọn ít nhất 1 thể loại'),
                    (v) => Array.isArray(v) && v.length > 0 ? null : 'Cần chọn ít nhất 1 thể loại',
                ],
                genre_id: [
                    (v) => validators.uuid(v, 'ID thể loại chính không hợp lệ'),
                ],
            };

            form.addEventListener('submit', async () => {
                if (!canManage) {
                    showFormMessage('Bạn không có quyền tạo phim.');
                    return;
                }

                clearFieldErrors(form);
                formMessage.classList.add('hidden');

                const categoryIds = Array.from(categorySelect.selectedOptions).map((opt) => opt.value);
                const payload = {
                    title: form.title.value.trim(),
                    name: form.name.value.trim(),
                    language: form.language.value.trim(),
                    status: form.status.value,
                    duration: form.duration.value,
                    age: form.age.value,
                    release_date: form.release_date.value,
                    end_date: form.end_date.value || null,
                    thumb_url: form.thumb_url.value.trim(),
                    trailer_url: form.trailer_url.value.trim(),
                    category_ids: categoryIds,
                    genre_id: form.genre_id.value || null,
                };

                const errors = validateValues(payload, schema);
                if (Object.keys(errors).length) {
                    showFieldErrors(form, errors);
                    showFormMessage('Vui lòng kiểm tra lại dữ liệu biểu mẫu.');
                    return;
                }

                payload.duration = Number(payload.duration);
                payload.age = Number(payload.age);

                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang tạo...';

                const result = await window.apiRequest('/api/movies', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                if (result.ok) {
                    showFormMessage(result.data?.message || 'Tạo phim thành công', 'success');
                    form.reset();
                    await loadMovies();
                } else if (result.status === 401) {
                    showFormMessage('Bạn cần đăng nhập để thực hiện thao tác này.');
                } else if (result.status === 403) {
                    showFormMessage('Bạn không có quyền tạo phim.');
                } else if (result.status === 422 && result.data?.errors) {
                    const backendErrors = normalizeApiErrors(result.data.errors);
                    showFieldErrors(form, backendErrors);
                    showFormMessage(window.firstApiError(result.data));
                } else {
                    showFormMessage(window.firstApiError(result.data));
                }

                submitBtn.disabled = false;
                submitBtn.textContent = 'Tạo phim';
            });

            await Promise.all([loadCategories(), loadMovies()]);
        });
    </script>
@endpush


