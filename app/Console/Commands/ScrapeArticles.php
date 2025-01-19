<?php

namespace App\Console\Commands;

use App\Models\Article;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Str;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class ScrapeArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:all-articles';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Scrape articles from all predefined sources and categories and save them to the database in a performant way';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define sources and categories
        $baseUrls = [
            'bbc' => [
                'travel' => 'https://www.bbc.com/travel',
                'business' => 'https://www.bbc.com/business',
                'news' => 'https://www.bbc.com/news',
            ],
            'theguardian' => [
                'politics' => 'https://www.theguardian.com/politics',
                'sports' => 'https://www.theguardian.com/sport',
                'culture' => 'https://www.theguardian.com/culture',
            ],
            'nytimes' => [
                'world' => 'https://www.nytimes.com/section/world',
                'business' => 'https://www.nytimes.com/section/business',
                'arts' => 'https://www.nytimes.com/spotlight/lifestyle',
            ],
        ];

        // Initialize the browser
        $browser = new HttpBrowser(HttpClient::create([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ],
        ]));

        $allArticles = [];

        // Loop through each source and category
        foreach ($baseUrls as $source => $categories) {
            foreach ($categories as $category => $url) {
                $this->info("Scraping {$source} - {$category}...");
                try {
                    $crawler = $browser->request('GET', $url);
                    $articles = $this->scrapeArticles($crawler, $source, $category);
                    $allArticles = array_merge($allArticles, $articles);
                    $this->info("Scraped " . count($articles) . " articles from {$source} - {$category}.");
                } catch (\Exception $e) {
                    $this->error("Failed to scrape {$source} - {$category}: " . $e->getMessage());
                }
            }
        }

        // $this->info("Successfully saved " . count($allArticles) . " articles.");

        // Save all articles in chunks with a transaction
        $this->saveArticles($allArticles);
    }

    /**
     * Scrape articles based on source
     */
    private function scrapeArticles(Crawler $crawler, string $source, string $category): array
    {
        $articles = [];

        switch ($source) {
            case 'bbc':
                $articleHtmlElements = $crawler->filter('.sc-b8778340-3');
                foreach ($articleHtmlElements as $articleHtmlElement) {
                    $articleCrawler = new Crawler($articleHtmlElement);
                    if ($articleCrawler->filter('.sc-8ea7699c-1')->count() > 0) {
                        $title = $articleCrawler->filter('.sc-8ea7699c-1')->text();
                        $description = $articleCrawler->filter('.sc-b8778340-4')->text();
                        $articles[] = [
                            'title' => $title,
                            'description' => $description,
                            'author' => strtoupper($source) . ' Staff',
                            'source' => $source,
                            'category' => $category,
                            'publish_date' => Carbon::now()->format('Y-m-d H:i:s'), // Format the date
                            // 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),   // Format the date
                        ];
                    }
                }
                break;

            case 'theguardian':
                $articleHtmlElements = $crawler->filter('.dcr-f9aim1');
                foreach ($articleHtmlElements as $articleHtmlElement) {
                    $articleCrawler = new Crawler($articleHtmlElement);
                    // if ($articleCrawler->filter('.fc-item__title')->count() > 0) {
                    $title = $articleCrawler->filter('.card-headline')->text();
                    // $description = $articleCrawler->filter('.fc-item__standfirst')->count() > 0
                    //     ? $articleCrawler->filter('.fc-item__standfirst')->text()
                    //     : 'No description available.';
                    $articles[] = [
                        'title' => $title,
                        'description' => $title,
                        'author' => strtoupper($source) . ' Staff',
                        'source' => $source,
                        'category' => $category,
                        'publish_date' => Carbon::now()->format('Y-m-d H:i:s'), // Format the date
                        // 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),   // Format the date
                    ];
                    // }
                }
                break;

            case 'nytimes':
                $articleHtmlElements = $crawler->filter('.css-14ee9cx');
                foreach ($articleHtmlElements as $article_html_element) {
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
                        'publish_date' => Carbon::now()->format('Y-m-d H:i:s'), // Format the date
                        // 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),   // Format the date
                        //  'datePublished' => $raw_date,
                    ];
                }
                break;
        }

        return $articles;
    }

    /**
     * Save articles to the database in a performant way
     */

    private function saveArticles(array $articles): void
    {
        if (empty($articles)) {
            $this->info("No articles to save.");
            return;
        };

        // Chunk articles to avoid memory overload and long transaction times
        $chunkSize = 500; 
        $chunks = array_chunk($articles, $chunkSize);
        try {
            // Wrap the whole operation in a transaction to ensure data integrity
            DB::transaction(function () use ($chunks) {
                foreach ($chunks as $chunk) {
                    // Perform a bulk insert
                    Article::insert($chunk);  
                }
            });

            $this->info("Successfully saved " . count($articles) . " articles.");
        } catch (\Exception $e) {
            $this->error("Failed to save articles: " . $e->getMessage());
        }
    }

    //     private function saveArticles(array $articles): void
// {
//     if (empty($articles)) {
//         $this->info("No articles to save.");
//         return;
//     }

    //     $this->info("Saving articles...");
//     // Chunk articles to avoid memory overload and long transaction times
//     $chunkSize = 500; // Adjust based on your memory and database performance
//     $chunks = array_chunk($articles, $chunkSize);

    //     try {
//         // Wrap the whole operation in a transaction to ensure data integrity
//         DB::transaction(function () use ($chunks) {
//             foreach ($chunks as $chunk) {
//                 foreach ($chunk as $article) {
//                     // Check if the article already exists by title and source
//                     $existingArticle = Article::where('title', $article['title'])
//                         ->where('source', $article['source'])
//                         ->first();

    //                     if ($existingArticle) {
//                         // If article exists, update it
//                         $existingArticle->update([
//                             'description' => $article['description'],
//                             'author' => $article['author'],
//                             'category' => $article['category'],
//                             'updated_at' => now(),
//                         ]);
//                     } else {
//                         // If article doesn't exist, insert it
//                         Article::create($article);
//                     }
//                 }
//             }
//         });

    //         $this->info("Successfully saved " . count($articles) . " articles.");
//     } catch (\Exception $e) {
//         $this->error("Failed to save articles: " . $e->getMessage());
//     }
// }
    // private function saveArticles(array $articles): void
    // {
    //     if (empty($articles)) {
    //         $this->info("No articles to save.");
    //         return;
    //     }

    //      $this-> info("Save articles");
    //     // Chunk articles to avoid memory overload and long transaction times
    //     $chunkSize = 500; // You can adjust the chunk size based on your memory and database performance
    //     $chunks = array_chunk($articles, $chunkSize);

    //     try {
    //         // Wrap the whole operation in a transaction to ensure data integrity
    //         DB::transaction(function () use ($chunks) {
    //             foreach ($chunks as $chunk) {
    //                 Article::upsert($chunk, ['title', 'source'], ['description', 'author', 'category', 'updated_at']);
    //                 // Article::upsert($chunk, ['title'], ['description', 'author', 'source', 'category', 'updated_at']);
    //             }
    //         });

    //         $this->info("Successfully saved " . count($articles) . " articles.");
    //     } catch (\Exception $e) {
    //         $this->error("Failed to save articles: " . $e->getMessage());
    //     }
    // }

}
