<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Loader\FilesystemLoader;
use Tuupola\Middleware\CorsMiddleware;



$app = AppFactory::create();


$app->add(new CorsMiddleware([
    "origin" => ["*"], // Allows all origins. You can restrict this to specific domains.
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
    "headers.allow" => ["Content-Type", "Authorization"],
    "headers.expose" => ["Authorization"],
    "headers.max_age" => 86400,
    "credentials" => true
]));

$baseUrl = "https://komikcast.cz";

$loader = new FilesystemLoader(__DIR__ . '/views');
$twig = new Twig($loader, [
    'cache' => __DIR__ . '/cache',
]);

$app->add(TwigMiddleware::create($app, $twig));



$app->get('/', function ($request, $response, $args) use ($twig) {
    return $twig->render($response, 'home.html');
});

function responseApi(Response $res, int $status, string $message, array $data = []): Response {
    $payload = json_encode([
        'status' => $status === 200 ? 'success' : 'error',
        'message' => $message,
        'data' => $data
    ]);

    $res->getBody()->write($payload);
    return $res->withHeader('Content-Type', 'application/json')->withStatus($status);
}

$app->get('/terbaru', function (Request $req, Response $res) use ($baseUrl) {
    try {
        $queryParams = $req->getQueryParams();
        $page = $queryParams['page'] ?? null;

        if ($page === null) {
            return responseApi($res, 500, "page is required");
        }

        $client = new Client();
        $response = $client->get("{$baseUrl}/project-list/page/{$page}");

        if ($response->getStatusCode() === 200) {
            $komikList = [];
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $element = $crawler->filter('#content > .wrapper > .postbody > .bixbox');

            $current_page = trim($element->filter('.pagination > .page-numbers.current')->text());

            $length_page = null;
            for ($i = 6; $i <= 11; $i++) {
                if ($element->filter(".pagination > .page-numbers:nth-child({$i})")->attr('class') === 'next page-numbers') {
                    $length_page = trim($element->filter(".pagination > .page-numbers:nth-child(" . ($i - 1) . ")")->text());
                    break;
                }
                if ($element->filter(".pagination > .page-numbers:nth-child({$i})")->attr('class') === 'page-numbers current') {
                    $length_page = trim($element->filter(".pagination > .page-numbers:nth-child({$i})")->text());
                    break;
                }
            }

            $element->filter('.list-update_items > .list-update_items-wrapper > .list-update_item')->each(function ($node) use (&$komikList, $baseUrl) {
                $thumbnail = $node->filter('a > .list-update_item-image > img')->attr('src') ?: $node->filter('a > .list-update_item-image > img')->attr('data-src');
                $href = $node->filter('a')->attr('href');
                $type = trim($node->filter('a > .list-update_item-image > .type')->text());
                $title = trim($node->filter('a > .list-update_item-info > h3')->text());
                $chapter = trim($node->filter('a > .list-update_item-info > .other > .chapter')->text());
                $rating = trim($node->filter('a > .list-update_item-info > .other > .rate > .rating > .numscore')->text());

                $komikList[] = [
                    'title' => $title,
                    'href' => trim(str_replace("{$baseUrl}/komik", '', $href)),
                    'thumbnail' => $thumbnail,
                    'type' => $type,
                    'chapter' => $chapter,
                    'rating' => $rating
                ];
            });

            return responseApi($res, 200, "success", [
                'current_page' => floatval($current_page),
                'length_page' => floatval($length_page),
                'data' => $komikList
            ]);
        }

        return responseApi($res, $response->getStatusCode(), "failed");
    } catch (Exception $e) {
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/genre', function (Request $req, Response $res) use ($baseUrl) {
    try {
        $client = new Client();
        $response = $client->get($baseUrl);

        if ($response->getStatusCode() === 200) {
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $element = $crawler->filter('#content > .wrapper');
            $komikList = [];

            $element->filter('#sidebar > .section > ul.genre > li')->each(function ($node) use (&$komikList, $baseUrl) {
                $title = trim($node->filter('a')->text());
                $href = trim(str_replace("{$baseUrl}/genres", '', $node->filter('a')->attr('href')));

                $komikList[] = [
                    'title' => $title,
                    'href' => $href
                ];
            });

            return responseApi($res, 200, "success", $komikList);
        }

        return responseApi($res, $response->getStatusCode(), "failed");
    } catch (Exception $e) {
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/genres/{genre}/{page}', function (Request $req, Response $res, array $args) use ($baseUrl) {
    try {
        $genre = $args['genre'];
        $page = $args['page'];

        $client = new Client();
        $response = $client->get("{$baseUrl}/genres/{$genre}/page/{$page}");

        if ($response->getStatusCode() === 200) {
            $komikList = [];
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $element = $crawler->filter('#content > .wrapper > .postbody > .bixbox');

            $checkPagination = trim($element->filter('.listupd > .list-update_items > .pagination > .current')->text());

            $paginationItems = $element->filter('.pagination > .page-numbers');
            $length_page = trim($paginationItems->eq($paginationItems->count() - 2)->text());

            $element->filter('.listupd > .list-update_items > .list-update_items-wrapper > .list-update_item')->each(function ($node) use (&$komikList, $baseUrl) {
                $title = trim($node->filter('a > .list-update_item-info > h3')->text());
                $chapter = trim($node->filter('a > .list-update_item-info > .other > .chapter')->text());
                $type = trim($node->filter('a > .list-update_item-image > .type')->text());
                $thumbnail = $node->filter('a > .list-update_item-image > img')->attr('src') ?: $node->filter('a > .list-update_item-image > img')->attr('data-src');
                $rating = trim($node->filter('a > .list-update_item-info > .other > .rate > .rating > .numscore')->text());
                $href = $node->filter('a')->attr('href');

                $komikList[] = [
                    'title' => $title,
                    'chapter' => $chapter,
                    'type' => $type,
                    'href' => trim(str_replace("{$baseUrl}/komik", '', $href)),
                    'rating' => $rating,
                    'thumbnail' => $thumbnail,
                ];
            });

            return responseApi($res, 200, "success", [
                'current_page' => $checkPagination === "" ? 1 : (int) $checkPagination,
                'length_page' => $length_page === "" ? 1 : (int) $length_page,
                'data' => $komikList,
            ]);
        }

        return responseApi($res, $response->getStatusCode(), $response->getReasonPhrase());
    } catch (Exception $e) {
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/read/{url:.*}', function (Request $req, Response $res, array $args) use ($baseUrl) {
    try {
        $url = $args['url'];
        $client = new Client();
        $response = $client->get("{$baseUrl}/{$url}");
        $komikList = [];

        if ($response->getStatusCode() === 200) {
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $element = $crawler->filter('#content > .wrapper');
            $title = null;
            $panel = [];
            $prevChapter = null;
            $nextChapter = null;

            // Check for previous chapter
            $prevChapterElement = $element->filter('.chapter_nav-control > .right-control > .nextprev > a[rel="prev"]');
            if ($prevChapterElement->count() > 0) {
                $prevChapter = trim(str_replace("{$baseUrl}/chapter", "", $prevChapterElement->attr('href')));
            }

            // Check for next chapter
            $nextChapterElement = $element->filter('.chapter_nav-control > .right-control > .nextprev > a[rel="next"]');
            if ($nextChapterElement->count() > 0) {
                $nextChapter = trim(str_replace("{$baseUrl}/chapter", "", $nextChapterElement->attr('href')));
            }

            $title = trim($element->filter('.chapter_headpost > h1')->text());
            $element->filter('.chapter_ > #chapter_body > .main-reading-area > img')->each(function ($node) use (&$panel) {
                $panel[] = $node->attr('src');
            });

            $komikList[] = [
                'title' => $title,
                'prev' => $prevChapter,
                'next' => $nextChapter,
                'panel' => $panel,
            ];

            // Logging for debugging
            error_log("Fetched chapter: $url, Title: $title, Prev: $prevChapter, Next: $nextChapter");

            return responseApi($res, 200, "success", $komikList);
        }

        return responseApi($res, $response->getStatusCode(), "failed");
    } catch (Exception $e) {
        // Logging the exception
        error_log("Exception occurred: " . $e->getMessage());
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/search', function (Request $req, Response $res) use ($baseUrl) {
    try {
        $keyword = $req->getQueryParams()['keyword'] ?? null;
        if ($keyword === null) {
            return responseApi($res, 500, "keyword is required");
        }

        $client = new Client();
        $response = $client->get("{$baseUrl}/?s={$keyword}");
        $komikList = [];

        if ($response->getStatusCode() === 200) {
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $element = $crawler->filter('#content > .wrapper > .postbody > .dev > #main > .list-update');

            $element->filter('.list-update_items > .list-update_items-wrapper > .list-update_item')->each(function ($node) use (&$komikList, $baseUrl) {
                $title = trim($node->filter('a > .list-update_item-info > h3')->text());
                $href = $node->filter('a')->attr('href');
                $type = trim($node->filter('a > .list-update_item-image > .type')->text());
                $rating = trim($node->filter('a > .list-update_item-info > .other > .rate > .rating > .numscore')->text());
                $chapter = trim($node->filter('a > .list-update_item-info > .other > .chapter')->text());
                $thumbnail = $node->filter('a > .list-update_item-image > img')->attr('src') ?: $node->filter('a > .list-update_item-image > img')->attr('data-src');

                $komikList[] = [
                    'title' => $title,
                    'type' => $type,
                    'chapter' => $chapter,
                    'rating' => $rating,
                    'href' => trim(str_replace("{$baseUrl}/komik", '', $href)),
                    'thumbnail' => $thumbnail,
                ];
            });

            return responseApi($res, 200, "success", $komikList);
        }

        return responseApi($res, $response->getStatusCode(), "failed");
    } catch (Exception $e) {
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/detail/{url:.*}', function (Request $req, Response $res, array $args) use ($baseUrl) {
    try {
        $url = $args['url'];
        $client = new Client();
        $response = $client->get("{$baseUrl}/komik/{$url}");

        if ($response->getStatusCode() !== 200) {
            return responseApi($res, 500, "Failed to get data from external source");
        }

        $komikList = [];
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);
        $element = $crawler->filter("#content > .wrapper > .komik_info");

        $title = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > h1")->text());
        $altTitle = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > .komik_info-content-native")->text());
        $thumbnail = $element->filter(".komik_info-cover-box > .komik_info-cover-image > img")->attr('src') ?? $element->filter(".komik_info-cover-box > .komik_info-cover-image > img")->attr('data-src');
        $description = trim($element->filter(".komik_info-description > .komik_info-description-sinopsis > p")->text());
        $status = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > .komik_info-content-meta > span:nth-child(3)")->text());
        $type = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > .komik_info-content-meta > span:nth-child(4)")->text());
        $released = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > .komik_info-content-meta > span:nth-child(1)")->text());
        $author = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > .komik_info-content-meta > span:nth-child(2)")->text());
        $updatedOn = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > .komik_info-content-meta > .komik_info-content-update")->text());
        $rating = trim($element->filter(".komik_info-body > .komik_info-content > .komik_info-content-rating > .komik_info-content-rating-bungkus > .data-rating > strong")->text());

        $chapters = [];
        $element->filter(".komik_info-body > .komik_info-chapters > ul > li")->each(function ($node) use (&$chapters, $baseUrl) {
            $title = trim($node->filter("a")->text());
            $href = $node->filter("a")->attr('href') ?? $node->filter("a:nth-child(2)")->attr('href');
            $date = trim($node->filter(".chapter-link-time")->text());
            $chapters[] = [
                'title' => "Chapter " . trim(str_replace("Chapter", "", $title)),
                'href' => trim(str_replace("{$baseUrl}/chapter", "", $href)),
                'date' => $date
            ];
        });

        $genres = [];
        $element->filter(".komik_info-body > .komik_info-content > .komik_info-content-body > .komik_info-content-genre > a")->each(function ($node) use (&$genres, $baseUrl) {
            $genres[] = [
                'title' => trim($node->text()),
                'href' => trim(str_replace("{$baseUrl}/genres", "", $node->attr('href')))
            ];
        });

        $komikList[] = [
            'title' => $title,
            'altTitle' => $altTitle,
            'updatedOn' => trim(str_replace("Updated on:", "", $updatedOn)),
            'rating' => trim(str_replace("Rating ", "", $rating)),
            'status' => trim(str_replace("Status:", "", $status)),
            'type' => trim(str_replace("Type:", "", $type)),
            'released' => trim(str_replace("Released:", "", $released)),
            'author' => trim(str_replace("Author:", "", $author)),
            'genre' => $genres,
            'description' => $description,
            'thumbnail' => $thumbnail,
            'chapter' => $chapters
        ];

        return responseApi($res, 200, "success", $komikList[0]);
    } catch (Exception $e) {
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/popular', function (Request $req, Response $res) use ($baseUrl) {
    try {
        $client = new Client();
        $response = $client->get($baseUrl);

        if ($response->getStatusCode() !== 200) {
            return responseApi($res, $response->getStatusCode(), "failed");
        }

        $komikList = [];
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);
        $element = $crawler->filter("#content > .wrapper > #sidebar");

        $element->filter(".section > .widget-post > .serieslist.pop > ul > li")->each(function ($node) use (&$komikList, $baseUrl) {
            $title = trim($node->filter(".leftseries > h2 > a")->text());
            $year = trim($node->filter(".leftseries > span:nth-child(3)")->text());
            $genre = trim($node->filter(".leftseries > span:nth-child(2)")->text());
            $thumbnail = $node->filter(".imgseries > a > img")->attr('src') ?? $node->filter(".imgseries > a > img")->attr('data-src');
            $href = trim($node->filter(".imgseries > a")->attr('href'));
            $komikList[] = [
                'title' => $title,
                'href' => trim(str_replace("{$baseUrl}/komik", "", $href)),
                'genre' => trim(str_replace("Genres:", "", $genre)),
                'year' => $year,
                'thumbnail' => $thumbnail
            ];
        });

        return responseApi($res, 200, "success", $komikList);
    } catch (Exception $e) {
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/recommended', function (Request $req, Response $res) use ($baseUrl) {
    try {
        $client = new Client();
        $response = $client->get($baseUrl);

        if ($response->getStatusCode() !== 200) {
            return responseApi($res, $response->getStatusCode(), "failed");
        }

        $komikList = [];
        $html = (string) $response->getBody();
        $crawler = new Crawler($html);
        $element = $crawler->filter("#content > .wrapper > .bixbox > .listupd > .swiper > .swiper-wrapper > .swiper-slide");

        $element->each(function ($node) use (&$komikList, $baseUrl) {
            $title = trim($node->filter("a > .splide__slide-info > .title")->text());
            $rating = trim($node->filter("a > .splide__slide-info > .other > .rate > .rating > .numscore")->text());
            $chapter = trim($node->filter("a > .splide__slide-info > .other > .chapter")->text());
            $type = trim($node->filter("a > .splide__slide-image  > .type")->text());
            $href = $node->filter("a")->attr('href');
            $thumbnail = $node->filter("a > .splide__slide-image > img")->attr('src') ?: $node->filter("a > .splide__slide-image > img")->attr('data-src');

            $komikList[] = [
                'title' => $title,
                'href' => trim(str_replace("{$baseUrl}/komik", "", $href)),
                'rating' => $rating,
                'thumbnail' => $thumbnail,
                'chapter' => $chapter,
                'type' => $type,
            ];
        });

        $filteredKomikList = array_filter($komikList, function($v) {
            return isset($v['href']);
        });

        return responseApi($res, 200, "success", array_values($filteredKomikList));
    } catch (Exception $e) {
        return responseApi($res, 500, $e->getMessage());
    }
});

$app->get('/daftar-komik/{page}', function (Request $req, Response $res, array $args) use ($baseUrl) {
    try {
        $client = new Client();
        $currentPage = $args['page'];
        
        // Determine the URL based on the current page
        $url = $currentPage === "1" ? "{$baseUrl}/daftar-komik/" : "{$baseUrl}/daftar-komik/page/{$currentPage}";
        $response = $client->get($url);

        $komikList = [];
        $pagination = [];

        if ($response->getStatusCode() === 200) {
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            // Select the elements containing the comic data
            $element = $crawler->filter("#content > .wrapper > .komiklist > .komiklist_filter > .list-update > .list-update_items > .list-update_items-wrapper > .list-update_item");

            $element->each(function ($node) use (&$komikList, &$pagination, $baseUrl) {
                $title = trim($node->filter("a > .list-update_item-info > h3")->text());
                $chapter = trim($node->filter("a > .list-update_item-info > .other > .chapter")->text());
                $type = trim($node->filter("a > .list-update_item-image > .type")->text());
                $thumbnail = $node->filter("a > .list-update_item-image > img")->attr('src');
                $rating = trim($node->filter("a > .list-update_item-info > .other > .rate > .rating > .numscore")->text());
                $href = $node->filter("a")->attr('href');

                // Update pagination info
                $paginationElement = $node->filter(".pagination");

                $paginationElement->filter("a.page-numbers")->each(function ($el) use (&$pagination) {
                    $page = trim($el->text());
                    $url = $el->attr('href');
                    $pagination[$page] = $url;
                });

                $komikList[] = [
                    'title' => $title,
                    'chapter' => $chapter,
                    'type' => $type,
                    'thumbnail' => $thumbnail,
                    'rating' => $rating,
                    'href' => trim(str_replace("{$baseUrl}/komik", "", $href)),
                ];
            });

            // Return the API response
            return responseApi($res, 200, "success", [
                'comics' => array_filter($komikList, function ($v) {
                    return isset($v['href']);
                }),
                'halaman' => $pagination,
            ]);
        }

        return responseApi($res, $response->getStatusCode(), "failed");
    } catch (\Exception $e) {
        return responseApi($res, 402, "failed");
    }
});




return $app;

