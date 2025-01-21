<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{

    //* Fetch all articles. Search articles by title or description. Filter by category, author and date
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

        if ($request->has('search_term')) {
            $searchTerm = $request->input('search_term');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        $articles = $query->paginate(10); // Paginate results

        return response()->json($articles, 200);
    }

    /**
     * Fetch articles based on the authenticated user's preferences.
     */
    public function fetchByPreference(Request $request)
    {
        try {

            $preferences = $request->user()->preferences;

            $query = Article::query();

            // Apply preferences to the query
            $preferences->each(function ($preference) use ($query) {
                if ($preference->source) {
                    $query->orWhere('source', $preference->source);
                }
                if ($preference->category) {
                    $query->orWhere('category', $preference->category);
                }
                if ($preference->author) {
                    $query->orWhere('author', $preference->author);
                }
            });

            $articles = $query->paginate(10);

            return response()->json($articles, 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching prefered articles: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch prefered articles.'], 500);
        }
    }
}
