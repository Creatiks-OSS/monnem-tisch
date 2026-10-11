<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerSegment;
use App\Models\Order;

class CustomerSegmentService
{
    public function cartAbandoners(): CustomerSegment
    {
        return CustomerSegment::query()->firstOrCreate(
            ['slug' => CustomerSegment::CART_LEFT],
            [
                'name' => 'Sepette ürün bırakanlar',
                'description' => 'Ödemeyi tamamlamadan sepetini bırakan müşteriler. Satın alma tamamlanınca listeden çıkar.',
            ]
        );
    }

    public function syncOrder(Order $order): void
    {
        if (! $order->customer_id) {
            return;
        }

        $customer = $order->customer ?: Customer::query()->find($order->customer_id);
        if (! $customer) {
            return;
        }

        if ($order->isAbandoned()) {
            $this->cartAbandoners()->customers()->syncWithoutDetaching([$customer->id]);

            return;
        }

        $this->release($customer);
    }

    public function release(Customer $customer): void
    {
        $stillAbandoned = Order::query()
            ->abandoned()
            ->where('customer_id', $customer->id)
            ->exists();

        if ($stillAbandoned) {
            return;
        }

        $this->cartAbandoners()->customers()->detach($customer->id);
    }

    public function syncAbandonedMemberships(): CustomerSegment
    {
        $segment = $this->cartAbandoners();
        $customerIds = Order::query()
            ->abandoned()
            ->whereNotNull('customer_id')
            ->pluck('customer_id')
            ->unique()
            ->values();

        if ($customerIds->isNotEmpty()) {
            $segment->customers()->syncWithoutDetaching($customerIds->all());
        }

        $memberIds = $segment->customers()->pluck('customers.id');
        $remove = $memberIds->diff($customerIds);
        if ($remove->isNotEmpty()) {
            $segment->customers()->detach($remove->all());
        }

        return $segment->loadCount('customers');
    }
}
