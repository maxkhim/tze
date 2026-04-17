<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sku', 'price', 'stock_quantity', 'category'];

    public function scopeSearch($query, $term)
    {
        if ($term) {
            return $query->whereFullText(['name', 'sku'], $term);
        }
        return $query;
    }

    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::tags(['products'])->flush();
        });

        static::deleted(function () {
            Cache::tags(['products'])->flush();
        });
    }
}