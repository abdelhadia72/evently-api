<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public static $cacheKey = 'categories';

    protected $fillable = [
        'name',
        'description',
    ];

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ];
    }
}
