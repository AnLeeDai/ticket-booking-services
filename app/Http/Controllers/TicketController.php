<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTicketRequest;
use App\Services\TicketServices;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        protected TicketServices $ticketServices
    ) {}

    public function index(Request $request)
    {
        return $this->ticketServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->ticketServices->getById($id);
    }

    public function myTickets(Request $request)
    {
        return $this->ticketServices->myTickets($request);
    }

    public function book(CreateTicketRequest $request)
    {
        return $this->ticketServices->bookTicket($request);
    }

    public function confirmPayment(string $id)
    {
        return $this->ticketServices->confirmPayment($id);
    }

    public function cancel(string $id, Request $request)
    {
        return $this->ticketServices->cancelTicket($id, $request);
    }
}
