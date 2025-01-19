<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class ScrappingController extends Controller
{

    // public function scrapeQuotes(): JsonResponse
    // {
    //     // initialize a browser-like HTTP client
    //     $browser = new HttpBrowser(HttpClient::create());
    //     // download and parse the HTML of the target page
    //     $crawler = $browser->request('GET', 'https://quotes.toscrape.com/');
    //     $quotes = [];

    //     // select all quote HTML elements on the page
    //     $quote_html_elements = $crawler->filter('.quote');
    //     // var_dump($quote_html_elements);
    //     // iterate over each quote HTML element and apply the scraping logic
    //     foreach ($quote_html_elements as $quote_html_element) {
    //         // create a new quote crawler
    //         $quote_crawler = new Crawler($quote_html_element);
    //         // perform the data extraction logic
    //         $text_html_element = $quote_crawler->filter('.text');
    //         $raw_text = $text_html_element->text();
    //         // remove special characters from the raw text information
    //         $text = str_replace(["\u{201c}", "\u{201d}"], '', $raw_text);
    //         $author_html_element = $quote_crawler->filter('.author');
    //         $author = $author_html_element->text();
    //         $tag_html_elements = $quote_crawler->filter('.tag');
    //         $tags = [];

    //         foreach ($tag_html_elements as $tag_html_element) {
    //             $tag = $tag_html_element->textContent;
    //             $tags[] = $tag;
    //         }

    //         // create a new quote object with the scraped data
    //         $quote = [
    //             'text' => $text,
    //             'author' => $author,
    //             'tags' => $tag
    //         ];

    //         // add the quote object to the quotes array
    //         $quotes[] = $quote;
    //     }
    //     // var_dump($quotes);
    //     return response()->json(['quotes' => $quotes,]);
    // }

    public function scrapeArticles(Request $request): JsonResponse
    {
        // Get source URL and category from query parameters
        $source = $request->query('source');
        $category = $request->query('category');

        if (!in_array($source, ['bbc', 'theguardian', 'nytimes'])) {
            return response()->json(['error' => 'Invalid source provided. Allowed sources: bbc, theguardian, nytimes.'], 400);
        }

        // Define base URLs for the sources
        // $baseUrls = [
        //     'bbc' => 'https://www.bbc.com',
        //     'theguardian' => 'https://www.theguardian.com',
        //     'nytimes' => 'https://www.nytimes.com',
        // ];

        $baseUrls = [
            'bbc' => [
                'travel' => 'https://www.bbc.com/travel',
                'business' => 'https://www.bbc.com/business',
                'technology' => 'https://www.bbc.com/technology',
            ],
            'theguardian' => [
                'politics' => 'https://www.theguardian.com/politics',
                'sports' => 'https://www.theguardian.com/sport',
                'culture' => 'https://www.theguardian.com/culture',
            ],
            'nytimes' => [
                'world' => 'https://www.nytimes.com/section/world',
                'business' => 'https://www.nytimes.com/sectioin/business',
                'arts' => 'https://www.nytimes.com/section/arts',
            ]
        ];

        // Construct the full URL
        // $url = rtrim($baseUrls[$source], '/') . '/' . ltrim($category, '/');

        // Initialize a browser-like HTTP client with User-Agent header
        $browser = new HttpBrowser(HttpClient::create([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]));

        // Fetch the HTML content of the target page
        try {
            $crawler = $browser->request('GET', $baseUrls[$source][$category]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch the webpage: ' . $e->getMessage()], 500);
        }

        $articles = [];

        // Apply scraping logic based on the source
        switch ($source) {
            case 'bbc':
                if ($crawler->filter('.sc-b8778340-3')->count() > 0) {
                    $article_html_elements = $crawler->filter('.sc-b8778340-3');
                    foreach ($article_html_elements as $article_html_element) {
                        $article_crawler = new Crawler($article_html_element);
                        if ($article_crawler->filter('.sc-8ea7699c-1')->count() > 0) {
                            $title = $article_crawler->filter('.sc-8ea7699c-1')->text();
                            $description = $article_crawler->filter('.sc-b8778340-4')->text();
                            // $raw_date = $article_crawler->filter('.sc-6fba5bd4-1')->text();
                            // Convert the relative date (e.g., "2 days ago") into a proper DateTime object
                            // $date_published = Carbon::parse($raw_date)->toDateString();  // This converts "2 days ago" to actual date-time
                            $articles[] = [
                                'title' => $title,
                                'description' => $description,
                                'source' => $source,
                                'category' => $category,
                                'author' => strtoupper($source) . ' Staff',
                                // 'datePublished' => $date_published, 
                            ];
                        }
                    }
                }
                break;

            case 'theguardian':
                if ($crawler->filter('.dcr-f9aim1')->count() > 0) {
                    $article_html_elements = $crawler->filter('.dcr-f9aim1');
                    foreach ($article_html_elements as $article_html_element) {
                        $article_crawler = new Crawler($article_html_element);
                        $title = $article_crawler->filter('.card-headline')->text();
                        // $description = $article_crawler->filter('.dcr-4v2oe6')->text();
                        // $raw_date = $article_crawler->filter('.css-e0xall')->text();
                        // $raw_author = $article_crawler->filter('.dcr-1lwi0ka')->text();
                        // $author = Str::replaceFirst('By ', '', $raw_author);
                        // Convert the relative date (e.g., "2 days ago") into a proper DateTime object
                        // $datePublished = Carbon::parse($raw_date)->toDateString();  // This converts "2 days ago" to actual date-time
                        $articles[] = [
                            'title' => $title,
                            'description' => $title,
                            // 'author' => $raw_author,
                            'author' => strtoupper($source) . ' Staff',
                            'source' => $source,
                            'category' => $category,
                            //  'datePublished' => $raw_date,
                        ];
                    }
                }
                break;

            case 'nytimes':
                if ($crawler->filter('.css-14ee9cx')->count() > 0) {
                    $article_html_elements = $crawler->filter('.css-14ee9cx');
                    foreach ($article_html_elements as $article_html_element) {
                        $article_crawler = new Crawler($article_html_element);
                        $title = $article_crawler->filter('.css-8hzhxf')->text();
                        $description = $article_crawler->filter('.css-1pga48a')->text();
                        // $raw_date = $article_crawler->filter('.css-e0xall')->text();
                        $raw_author = $article_crawler->filter('.css-1y3ykdt')->text();
                        $author = Str::replaceFirst('By ', '', $raw_author);
                        // Convert the relative date (e.g., "2 days ago") into a proper DateTime object
                        // $datePublished = Carbon::parse($raw_date)->toDateString();  // This converts "2 days ago" to actual date-time
                        $articles[] = [
                            'title' => $title,
                            'description' => $description,
                            'author' => $author,
                            'source' => $source,
                            'category' => $category,
                            //  'datePublished' => $raw_date,
                        ];
                    }
                }
                break;

            default:
                return response()->json(['error' => 'Invalid source provided.'], 400);
        }

        if (empty($articles)) {
            return response()->json(['message' => 'No articles found.'], 404);
        }
        return response()->json(['articles' => $articles]);
    }

}

// public function filterArticles(Request $request)
// {
//     $query = Article::query();

//     // Filter by source
//     if ($request->filled('source_id')) {
//         $query->where('source_id', $request->source_id);
//     }

//     // Filter by category
//     if ($request->filled('category_id')) {
//         $query->whereHas('categories', function ($q) use ($request) {
//             $q->where('id', $request->category_id);
//         });
//     }

//     // Filter by date range
//     if ($request->filled('start_date') && $request->filled('end_date')) {
//         $query->whereBetween('publish_date', [$request->start_date, $request->end_date]);
//     }

//     // Keyword search in title or description
//     if ($request->filled('keyword')) {
//         $query->where(function ($q) use ($request) {
//             $q->where('title', 'like', '%' . $request->keyword . '%')
//               ->orWhere('description', 'like', '%' . $request->keyword . '%');
//         });
//     }

//     // Pagination for performance
//     $articles = $query->with('source', 'categories')->paginate(10);

//     return response()->json($articles);
// }