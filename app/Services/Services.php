<?php

namespace App\Services;

use App\Traits\JsonResponse;
use App\Traits\QueryFilter;

abstract class Services
{
    use JsonResponse, QueryFilter;

    public function __construct() {}
}
