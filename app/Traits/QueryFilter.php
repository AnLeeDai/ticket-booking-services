<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * QUERY PARAMS HỖ TRỢ:
 *   ?search=keyword               Tìm kiếm LIKE
 *   ?{field}=value                Lọc chính xác
 *   ?{field}=val1,val2            Lọc WHERE IN
 *   ?sort_by=name&sort_order=asc  Sắp xếp
 *   ?per_page=20&page=2           Phân trang
 *   ?fields=id,name               Chọn cột
 *   ?with=role                    Eager load
 *   ?with_count=tickets           Đếm quan hệ
 *   ?date_from=...&date_to=...    Lọc theo ngày
 *   ?is_null=col&not_null=col     Lọc NULL
 *   ?has=relation                 Có quan hệ
 *   ?doesnt_have=relation         Không có quan hệ
 *   ?trashed=only|with            Xoá mềm
 *
 * SHORTCUTS (TRẢ VỀ JSON RESPONSE):
 *   filterAndPaginate()   Filter + phân trang
 *   filterAndGet()        Filter + lấy tất cả
 *   findById()            Tìm theo ID
 *   findFirst()           Tìm theo điều kiện
 *   countRecords()        Đếm bản ghi
 *   recordExists()        Kiểm tra tồn tại
 *   createRecord()        Tạo bản ghi mới
 *   updateRecord()        Cập nhật theo ID
 *   deleteRecord()        Xoá theo ID (soft/hard)
 *   restoreRecord()       Khôi phục bản ghi đã xoá mềm
 *   forceDeleteRecord()   Xoá vĩnh viễn
 */
trait QueryFilter
{
    // ===================== CORE =====================

    /**
     * Áp dụng tất cả bộ lọc từ request params.
     */
    protected function applyFilters(
        Builder|Model $query,
        Request $request,
        array $searchableFields = [],
        array $filterableFields = [],
        array $sortableFields = [],
        array $allowedRelations = [],
    ): Builder {
        $builder = $query instanceof Model ? $query->query() : $query;

        return $builder
            ->tap(fn () => $this->applySearch($builder, $request, $searchableFields))
            ->tap(fn () => $this->applyExactFilters($builder, $request, $filterableFields))
            ->tap(fn () => $this->applyNullFilters($builder, $request, $filterableFields))
            ->tap(fn () => $this->applyHasRelation($builder, $request, $allowedRelations))
            ->tap(fn () => $this->applySort($builder, $request, $sortableFields))
            ->tap(fn () => $this->applyRelations($builder, $request, $allowedRelations))
            ->tap(fn () => $this->applyWithCount($builder, $request, $allowedRelations))
            ->tap(fn () => $this->applySelect($builder, $request))
            ->tap(fn () => $this->applyDateRange($builder, $request))
            ->tap(fn () => $this->applySoftDelete($builder, $request));
    }

    /**
     * Bọc logic trong try-catch, tự động trả JsonResponse.
     */
    protected function tryCatch(\Closure $callback)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            $description = app()->isProduction() ? '' : $e->getMessage();

            return $this->serverErrorResponse(description: $description);
        }
    }

    /**
     * Parse chuỗi phân cách bởi dấu phẩy thành mảng.
     */
    private function parseCommaSeparated(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return array_map('trim', explode(',', $value));
    }

    /**
     * Lọc chỉ giữ các giá trị nằm trong whitelist.
     */
    private function filterWhitelist(array $requested, array $allowed): array
    {
        return array_values(array_intersect($requested, $allowed));
    }

    // ===================== FILTER METHODS =====================

    /**
     * ?search=keyword → Tìm kiếm LIKE trên nhiều cột.
     * Hỗ trợ tìm trong quan hệ: searchableFields = ['name', 'role.name']
     */
    protected function applySearch(Builder $builder, Request $request, array $searchableFields): Builder
    {
        $search = $request->query('search');

        if (! $search || empty($searchableFields)) {
            return $builder;
        }

        return $builder->where(function (Builder $q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);
                    $q->orWhereHas($relation, fn (Builder $sub) => $sub->where($column, 'LIKE', "%{$search}%"));
                } else {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * ?status=active          → WHERE status = 'active'
     * ?status=active,inactive → WHERE status IN ('active','inactive')
     */
    protected function applyExactFilters(Builder $builder, Request $request, array $filterableFields): Builder
    {
        foreach ($filterableFields as $field) {
            $value = $request->query($field);

            if (is_null($value)) {
                continue;
            }

            str_contains($value, ',')
                ? $builder->whereIn($field, explode(',', $value))
                : $builder->where($field, $value);
        }

        return $builder;
    }

    /**
     * ?is_null=deleted_at,avatar_url → WHERE ... IS NULL
     * ?not_null=email_verified_at    → WHERE ... IS NOT NULL
     */
    protected function applyNullFilters(Builder $builder, Request $request, array $filterableFields): Builder
    {
        foreach (['is_null' => 'whereNull', 'not_null' => 'whereNotNull'] as $param => $method) {
            $value = $request->query($param);
            if (! $value) {
                continue;
            }

            $columns = $this->filterWhitelist($this->parseCommaSeparated($value), $filterableFields);
            foreach ($columns as $column) {
                $builder->{$method}($column);
            }
        }

        return $builder;
    }

    /**
     * ?has=tickets,comments   → Chỉ lấy bản ghi CÓ quan hệ
     * ?doesnt_have=tickets    → Chỉ lấy bản ghi KHÔNG CÓ quan hệ
     */
    protected function applyHasRelation(Builder $builder, Request $request, array $allowedRelations): Builder
    {
        if (empty($allowedRelations)) {
            return $builder;
        }

        foreach (['has' => 'whereHas', 'doesnt_have' => 'whereDoesntHave'] as $param => $method) {
            $value = $request->query($param);
            if (! $value) {
                continue;
            }

            $relations = $this->filterWhitelist($this->parseCommaSeparated($value), $allowedRelations);
            foreach ($relations as $relation) {
                $builder->{$method}($relation);
            }
        }

        return $builder;
    }

    /**
     * ?sort_by=name&sort_order=asc → ORDER BY name ASC
     * Mặc định: ORDER BY created_at DESC
     */
    protected function applySort(Builder $builder, Request $request, array $sortableFields): Builder
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = strtolower($request->query('sort_order', 'desc'));

        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        if (! empty($sortableFields) && ! in_array($sortBy, $sortableFields)) {
            $sortBy = 'created_at';
        }

        return $builder->orderBy($sortBy, $sortOrder);
    }

    /**
     * ?with=role,categories → Eager load quan hệ (whitelist).
     */
    protected function applyRelations(Builder $builder, Request $request, array $allowedRelations): Builder
    {
        return $this->applyRelationMethod($builder, $request, $allowedRelations, 'with', 'with');
    }

    /**
     * ?with_count=tickets → Thêm cột tickets_count (whitelist).
     */
    protected function applyWithCount(Builder $builder, Request $request, array $allowedRelations): Builder
    {
        return $this->applyRelationMethod($builder, $request, $allowedRelations, 'with_count', 'withCount');
    }

    /**
     * Helper chung cho applyRelations & applyWithCount.
     */
    private function applyRelationMethod(Builder $builder, Request $request, array $allowed, string $param, string $method): Builder
    {
        $value = $request->query($param);

        if (! $value || empty($allowed)) {
            return $builder;
        }

        $valid = $this->filterWhitelist($this->parseCommaSeparated($value), $allowed);

        if (! empty($valid)) {
            $builder->{$method}($valid);
        }

        return $builder;
    }

    /**
     * ?fields=id,name,email → SELECT id, name, email
     */
    protected function applySelect(Builder $builder, Request $request): Builder
    {
        $fields = $request->query('fields');

        if (! $fields) {
            return $builder;
        }

        return $builder->select($this->parseCommaSeparated($fields));
    }

    /**
     * ?date_from=2026-01-01&date_to=2026-12-31&date_column=updated_at
     * Mặc định date_column = created_at
     */
    protected function applyDateRange(Builder $builder, Request $request): Builder
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $dateColumn = $request->query('date_column', 'created_at');

        // Whitelist date columns to prevent arbitrary column access
        $allowedDateColumns = ['created_at', 'updated_at', 'deleted_at', 'release_date', 'end_date', 'starts_at', 'ends_at', 'hire_date', 'sale_date', 'dob', 'hold_until'];
        if (! in_array($dateColumn, $allowedDateColumns, true)) {
            $dateColumn = 'created_at';
        }

        if ($dateFrom) {
            $builder->whereDate($dateColumn, '>=', $dateFrom);
        }

        if ($dateTo) {
            $builder->whereDate($dateColumn, '<=', $dateTo);
        }

        return $builder;
    }

    /**
     * ?trashed=only → Chỉ bản ghi đã xoá mềm
     * ?trashed=with → Bao gồm cả đã xoá mềm
     */
    protected function applySoftDelete(Builder $builder, Request $request): Builder
    {
        $trashed = $request->query('trashed');

        if (! $trashed) {
            return $builder;
        }

        $usesSoftDelete = in_array(
            SoftDeletes::class,
            class_uses_recursive($builder->getModel())
        );

        if (! $usesSoftDelete) {
            return $builder;
        }

        return match ($trashed) {
            'only' => $builder->onlyTrashed(),
            'with' => $builder->withTrashed(),
            default => $builder,
        };
    }

    // ===================== PAGINATION =====================

    /**
     * Phân trang — trả về format gọn cho ReactJS.
     *
     * Response: { items: [...], pagination: { current_page, per_page, total, last_page, has_more } }
     */
    protected function paginateQuery(Builder $builder, Request $request, int $defaultPerPage = 15): array
    {
        $perPage = min(max((int) $request->query('per_page', $defaultPerPage), 1), 100);
        $paginated = $builder->paginate($perPage);

        return [
            'items' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'has_more' => $paginated->hasMorePages(),
            ],
        ];
    }

    // ===================== SHORTCUTS (TRẢ VỀ JSON RESPONSE) =====================

    /**
     * Filter + phân trang → JsonResponse.
     *
     * Cách dùng:
     *   return $this->filterAndPaginate(query: $this->model, request: $request, ...);
     */
    protected function filterAndPaginate(
        Builder|Model $query,
        Request $request,
        array $searchableFields = [],
        array $filterableFields = [],
        array $sortableFields = [],
        array $allowedRelations = [],
        int $defaultPerPage = 15,
        string $message = 'Lấy danh sách thành công',
    ) {
        return $this->tryCatch(function () use ($query, $request, $searchableFields, $filterableFields, $sortableFields, $allowedRelations, $defaultPerPage, $message) {
            $builder = $this->applyFilters($query, $request, $searchableFields, $filterableFields, $sortableFields, $allowedRelations);
            $result = $this->paginateQuery($builder, $request, $defaultPerPage);

            return $this->successResponse(data: $result, message: $message);
        });
    }

    /**
     * Filter + lấy tất cả (không phân trang) → JsonResponse.
     *
     * Cách dùng:
     *   return $this->filterAndGet(query: $this->model, request: $request, limit: 50);
     */
    protected function filterAndGet(
        Builder|Model $query,
        Request $request,
        array $searchableFields = [],
        array $filterableFields = [],
        array $sortableFields = [],
        array $allowedRelations = [],
        ?int $limit = null,
        string $message = 'Lấy danh sách thành công',
    ) {
        return $this->tryCatch(function () use ($query, $request, $searchableFields, $filterableFields, $sortableFields, $allowedRelations, $limit, $message) {
            $builder = $this->applyFilters($query, $request, $searchableFields, $filterableFields, $sortableFields, $allowedRelations);

            if ($limit) {
                $builder->limit($limit);
            }

            return $this->successResponse(data: $builder->get(), message: $message);
        });
    }

    /**
     * Tìm theo ID → JsonResponse. Không thấy → 404.
     *
     * Cách dùng:
     *   return $this->findById($this->model, $id, relations: ['role']);
     */
    protected function findById(
        Model $model,
        string $id,
        array $relations = [],
        string $message = 'Lấy dữ liệu thành công',
        string $notFoundMessage = 'Không tìm thấy dữ liệu',
    ) {
        return $this->tryCatch(function () use ($model, $id, $relations, $message, $notFoundMessage) {
            $query = $model->query();

            if (! empty($relations)) {
                $query->with($relations);
            }

            $result = $query->find($id);

            return $result
                ? $this->successResponse(data: $result, message: $message)
                : $this->errorResponse(message: $notFoundMessage, code: 404);
        });
    }

    /**
     * Tìm bản ghi đầu tiên theo điều kiện → JsonResponse. Không thấy → 404.
     *
     * Cách dùng:
     *   return $this->findFirst($this->model, ['slug' => 'am-nhac'], relations: ['tickets']);
     */
    protected function findFirst(
        Model $model,
        array $conditions,
        array $relations = [],
        string $message = 'Lấy dữ liệu thành công',
        string $notFoundMessage = 'Không tìm thấy dữ liệu',
    ) {
        return $this->tryCatch(function () use ($model, $conditions, $relations, $message, $notFoundMessage) {
            $query = $model->query()->where($conditions);

            if (! empty($relations)) {
                $query->with($relations);
            }

            $result = $query->first();

            return $result
                ? $this->successResponse(data: $result, message: $message)
                : $this->errorResponse(message: $notFoundMessage, code: 404);
        });
    }

    /**
     * Đếm số bản ghi → JsonResponse.
     *
     * Cách dùng:
     *   return $this->countRecords($this->model, ['status' => 'active']);
     */
    protected function countRecords(
        Model $model,
        array $conditions = [],
        string $message = 'Đếm dữ liệu thành công',
    ) {
        return $this->tryCatch(function () use ($model, $conditions, $message) {
            $query = $model->query();

            if (! empty($conditions)) {
                $query->where($conditions);
            }

            return $this->successResponse(data: ['count' => $query->count()], message: $message);
        });
    }

    /**
     * Kiểm tra bản ghi tồn tại → JsonResponse.
     *
     * Cách dùng:
     *   return $this->recordExists($this->model, ['slug' => 'am-nhac']);
     */
    protected function recordExists(
        Model $model,
        array $conditions,
        string $message = 'Kiểm tra dữ liệu thành công',
    ) {
        return $this->tryCatch(function () use ($model, $conditions, $message) {
            return $this->successResponse(
                data: ['exists' => $model->query()->where($conditions)->exists()],
                message: $message
            );
        });
    }

    // ===================== CREATE / UPDATE / DELETE =====================

    /**
     * Tạo bản ghi mới → JsonResponse (201).
     *
     * Cách dùng:
     *   return $this->createRecord($this->model, [
     *       'name' => $request->name,
     *       'slug' => Str::slug($request->name),
     *   ]);
     */
    protected function createRecord(
        Model $model,
        array $data,
        string $message = 'Tạo dữ liệu thành công',
        string $failMessage = 'Tạo dữ liệu thất bại',
    ) {
        return $this->tryCatch(function () use ($model, $data, $message, $failMessage) {
            $result = $model->query()->create($data);

            return $result
                ? $this->successResponse(data: $result, message: $message, code: 201)
                : $this->errorResponse(message: $failMessage);
        });
    }

    /**
     * Cập nhật bản ghi theo ID → JsonResponse.
     *
     * Cách dùng:
     *   return $this->updateRecord($this->model, $id, [
     *       'name' => $request->name,
     *   ]);
     */
    protected function updateRecord(
        Model $model,
        string $id,
        array $data,
        string $message = 'Cập nhật dữ liệu thành công',
        string $notFoundMessage = 'Không tìm thấy dữ liệu',
    ) {
        return $this->tryCatch(function () use ($model, $id, $data, $message, $notFoundMessage) {
            $record = $model->query()->find($id);

            if (! $record) {
                return $this->errorResponse(message: $notFoundMessage, code: 404);
            }

            $record->update($data);

            return $this->successResponse(data: $record->fresh(), message: $message);
        });
    }

    /**
     * Xoá bản ghi theo ID → JsonResponse.
     * Nếu Model dùng SoftDeletes → xoá mềm, ngược lại → xoá vĩnh viễn.
     *
     * Cách dùng:
     *   return $this->deleteRecord($this->model, $id);
     */
    protected function deleteRecord(
        Model $model,
        string $id,
        string $message = 'Xoá dữ liệu thành công',
        string $notFoundMessage = 'Không tìm thấy dữ liệu',
    ) {
        return $this->tryCatch(function () use ($model, $id, $message, $notFoundMessage) {
            $record = $model->query()->find($id);

            if (! $record) {
                return $this->errorResponse(message: $notFoundMessage, code: 404);
            }

            $record->delete();

            return $this->successResponse(data: null, message: $message);
        });
    }

    /**
     * Khôi phục bản ghi đã xoá mềm theo ID → JsonResponse.
     * Yêu cầu Model dùng SoftDeletes.
     *
     * Cách dùng:
     *   return $this->restoreRecord($this->model, $id);
     */
    protected function restoreRecord(
        Model $model,
        string $id,
        string $message = 'Khôi phục dữ liệu thành công',
        string $notFoundMessage = 'Không tìm thấy dữ liệu đã xoá',
    ) {
        return $this->tryCatch(function () use ($model, $id, $message, $notFoundMessage) {
            $record = $model->query()->onlyTrashed()->find($id);

            if (! $record) {
                return $this->errorResponse(message: $notFoundMessage, code: 404);
            }

            $record->restore();

            return $this->successResponse(data: $record->fresh(), message: $message);
        });
    }

    /**
     * Xoá vĩnh viễn bản ghi (bỏ qua SoftDeletes) → JsonResponse.
     *
     * Cách dùng:
     *   return $this->forceDeleteRecord($this->model, $id);
     */
    protected function forceDeleteRecord(
        Model $model,
        string $id,
        string $message = 'Xoá vĩnh viễn dữ liệu thành công',
        string $notFoundMessage = 'Không tìm thấy dữ liệu',
    ) {
        return $this->tryCatch(function () use ($model, $id, $message, $notFoundMessage) {
            $record = $model->query()->withTrashed()->find($id);

            if (! $record) {
                return $this->errorResponse(message: $notFoundMessage, code: 404);
            }

            $record->forceDelete();

            return $this->successResponse(data: null, message: $message);
        });
    }
}
