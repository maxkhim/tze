<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $category = $request->query('category');
        $search   = $request->query('search');
        $page     = $request->query('page', 1);

        // Уникальный ключ кеша на основе параметров запроса
        $cacheKey = "products:category_{$category}:search_{$search}:page_{$page}";

        $products = Cache::tags(['products'])->remember($cacheKey, now()->addMinutes(10), function () use ($category, $search) {
            return Product::query()
                ->byCategory($category)
                ->search($search)
                ->paginate(20);
        });

        return ProductResource::collection($products);
    }
}