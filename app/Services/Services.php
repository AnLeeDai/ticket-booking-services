<?php

namespace App\Services;

use App\Traits\JsonResponse;

abstract class Services
{
    use JsonResponse;

    public function __construct() {}
}
