<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Raspadinha extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'price',
        'max_prize',
        'category',
        'backend_cost',
        'is_active',
        'sort_order',
        'win_chance_percentage',
    ];

    protected $casts = [
        'backend_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'win_chance_percentage' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
} 