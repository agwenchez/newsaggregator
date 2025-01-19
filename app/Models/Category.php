<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Define the many-to-many relationship with sources
    public function sources()
    {
        return $this->belongsToMany(Source::class)
                    ->withPivot('category_url')  // Attach category URL
                    ->withTimestamps();
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }

}
