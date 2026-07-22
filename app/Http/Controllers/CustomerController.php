<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('document_number', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.customers.index', [
            'title' => 'Clientes',
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        Customer::query()->create($this->validated($request));

        return back()->with('success', 'Cliente registrado.');
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));

        return back()->with('success', 'Cliente actualizado.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Cliente eliminado.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:V,E,J,G,P'],
            'document_number' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
        ]) + ['active' => $request->boolean('active', true)];
    }
}
