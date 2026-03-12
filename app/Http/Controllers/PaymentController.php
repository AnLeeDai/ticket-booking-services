<?php

namespace App\Http\Controllers;

use App\Services\PaymentServices;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentServices $paymentServices
    ) {}

    public function index(Request $request)
    {
        return $this->paymentServices->getAll($request);
    }

    public function show(Request $request, string $id)
    {
        return $this->paymentServices->getById($request, $id);
    }
}
