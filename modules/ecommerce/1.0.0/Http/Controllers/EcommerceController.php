<?php

namespace Esubiz\Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Esubiz\Modules\Ecommerce\Models\Product;

class EcommerceController extends Controller
{
    public function products(Request $request): View
    {
        $query = Product::query()
            ->with('category')
            ->orderByDesc('id');

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $products = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'ecommerce::products.index',
            compact('products', 'search', 'status')
        );
    }

    public function page(string $page = 'dashboard'): View
    {
        $allowed = [
            'dashboard',
            'products',
            'categories',
            'inventory',
            'collections',
            'orders',
            'customers',
            'discounts',
            'checkout-links',
            'shipping',
            'returns',
            'reviews',
            'currencies',
            'payments',
            'settings',
            'reports',
        ];

        abort_unless(
            in_array($page, $allowed, true),
            404
        );

        return view(
            'ecommerce::' . $page . '.index',
            [
                'ecommercePage' => $page,
            ]
        );
    }
}
