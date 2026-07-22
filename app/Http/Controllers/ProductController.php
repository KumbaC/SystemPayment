<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%");
            }))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('pages.products.index', [
            'title' => 'Inventario / Productos',
            'products' => $products,
        ]);
    }

    public function create()
    {
        return view('pages.products.form', [
            'title' => 'Nuevo Producto',
            'product' => new Product,
            'categories' => Category::query()->where('active', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Product::query()->create($data);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        return view('pages.products.form', [
            'title' => 'Editar Producto',
            'product' => $product,
            'categories' => Category::query()->where('active', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request));

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function quickUpdate(Request $request, Product $product)
    {
        $data = $request->validate([
            'price_usd' => ['required', 'numeric', 'min:0'],
            'has_vat' => ['required', 'boolean'],
        ]);

        $product->update([
            'price_usd' => $data['price_usd'],
            'has_vat' => $request->boolean('has_vat'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Ajuste rápido guardado correctamente.',
                'product_id' => $product->id,
                'price_usd' => (float) $product->price_usd,
                'has_vat' => (bool) $product->has_vat,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Ajuste rápido guardado correctamente.');
    }

    public function destroy(Product $product)
    {
        Product::query()->whereKey($product->getKey())->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($request->route('product'))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cost_usd' => ['required', 'numeric', 'min:0'],
            'price_usd' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'active' => ['boolean'],
            'has_vat' => ['boolean'],
        ]) + [
            'active' => $request->boolean('active', true),
            'has_vat' => $request->boolean('has_vat', true),
        ];
    }
}
