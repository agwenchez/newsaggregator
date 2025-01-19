<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function filterArticles(Request $request)
    {
        $query = Article::query();

        // Filter by source name
        if ($request->filled('source_name')) {
            $query->whereHas('source', function ($q) use ($request) {
                $q->where('name', $request->source_name);
            });
        }

        // Filter by category name
        if ($request->filled('category_name')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('name', $request->category_name);
            });
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('publish_date', [$request->start_date, $request->end_date]);
        }

        // Keyword search in title or description
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                    ->orWhere('description', 'like', '%' . $request->keyword . '%');
            });
        }

        // Pagination for performance
        $articles = $query->with('source', 'categories')->paginate(10);

        return response()->json($articles);
    }

    public function filter(Request $request)
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
