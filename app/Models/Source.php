<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'base_url'];

    // Define the many-to-many relationship with categories
    public function categories()
    {
        return $this->belongsToMany(Category::class)
                    ->withPivot('category_url')  // Attach category URL
                    ->withTimestamps();
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
