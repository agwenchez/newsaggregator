<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->has('author')) {
            $query->where('author', $request->input('author'));
        }

        if ($request->has('date_published')) {
            $query->whereDate('publish_date', $request->input('date_published'));
        }

        $articles = $query->paginate(10); // Paginate results

        return response()->json($articles, 200);
    }

    /**
     * Search articles by title or description.
     */
    public function search(Request $request)
    {
        $query = Article::query();

        if ($request->has('q')) {
            $searchTerm = $request->input('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        $articles = $query->paginate(10); // Paginate results

        return response()->json($articles, 200);
    }
}
