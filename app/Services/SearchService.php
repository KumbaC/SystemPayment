<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Collection;

class SearchService
{
    public function search(string $query, int $limit = 15): Collection
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return collect();
        }

        $results = collect();

        Product::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->each(fn ($item) => $results->push([
                'type' => 'Producto',
                'label' => $item->name,
                'meta' => "SKU: {$item->sku}",
                'url' => route('products.edit', $item),
            ]));

        Customer::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('document_number', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->each(fn ($item) => $results->push([
                'type' => 'Cliente',
                'label' => $item->name,
                'meta' => $item->fullDocument(),
                'url' => route('customers.index', ['search' => $item->name]),
            ]));

        Sale::query()
            ->where('invoice_number', 'like', "%{$query}%")
            ->orWhere('sale_number', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->each(fn ($item) => $results->push([
                'type' => 'Venta',
                'label' => $item->invoice_number,
                'meta' => $item->sale_date->format('d/m/Y'),
                'url' => route('sales.show', $item),
            ]));

        Purchase::query()
            ->where('purchase_number', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->each(fn ($item) => $results->push([
                'type' => 'Compra',
                'label' => $item->purchase_number,
                'meta' => $item->purchase_date->format('d/m/Y'),
                'url' => route('purchases.show', $item),
            ]));

        Supplier::query()
            ->where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->each(fn ($item) => $results->push([
                'type' => 'Proveedor',
                'label' => $item->name,
                'meta' => $item->rif ?? '',
                'url' => route('suppliers.index'),
            ]));

        User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->each(fn ($item) => $results->push([
                'type' => 'Usuario',
                'label' => $item->name,
                'meta' => $item->email,
                'url' => route('users.index'),
            ]));

        return $results->take($limit)->values();
    }
}
