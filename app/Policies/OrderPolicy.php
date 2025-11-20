<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin()
            || $user->id === $order->customer_id
            || ($user->isVendor() && $this->isVendorOrder($user, $order));
    }

    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin()
            || ($user->isVendor() && $this->isVendorOrder($user, $order));
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->isAdmin()
            || $user->id === $order->customer_id;
    }

    private function isVendorOrder(User $user, Order $order): bool
    {
        return $order->items()
            ->whereHas('product', function ($query) use ($user) {
                $query->where('vendor_id', $user->id);
            })
            ->exists();
    }
}
