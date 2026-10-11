<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->withCount([
                'orders as orders_count' => fn ($q) => $q->placed(),
                'quotes',
            ])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('email', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $customer = Customer::query()->create($data);
        $this->activity->log('Müşteri oluşturuldu', $customer);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Müşteri oluşturuldu.');
    }

    public function show(Customer $customer): View
    {
        $customer->load(['orders', 'quotes', 'addresses', 'invoices', 'segments']);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:customers,email,'.$customer->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $customer->update($data);
        $this->activity->log('Müşteri güncellendi', $customer);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Müşteri kaydedildi.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();
        $this->activity->log('Müşteri silindi', $customer);

        return redirect()->route('admin.customers.index')->with('success', 'Müşteri silindi.');
    }
}