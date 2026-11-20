<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Customers\Models\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * /api/v1 customers (Prompt 16): pagination + filtering; max per_page 100;
 * tutarlı JSON hata gövdesi middleware'de.
 */
class CustomerApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, $request->integer('per_page', 20)));

        $customers = Customer::query()
            ->when($request->string('search')->toString(), fn ($q, string $search) => $q->where('name', 'ilike', "%{$search}%"))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->string('city')))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $customers->getCollection()->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'city' => $customer->city,
                'email' => $customer->email,
                'archived' => $customer->archived_at !== null,
            ])->all(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'total' => $customers->total(),
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'city' => $customer->city,
                'email' => $customer->email,
            ],
        ]);
    }
}
