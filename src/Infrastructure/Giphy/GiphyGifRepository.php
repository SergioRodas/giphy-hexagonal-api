<?php

declare(strict_types=1);

namespace Infrastructure\Giphy;

use Domain\Gif\Entity\Gif;
use Domain\Gif\Exception\GifNotFound;
use Domain\Gif\Exception\GifProviderUnavailable;
use Domain\Gif\GifSearchResult;
use Domain\Gif\Repository\GifRepository;
use Domain\Gif\ValueObject\GifId;
use Domain\Gif\ValueObject\SearchCriteria;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

/**
 * GifRepository adapter backed by the GIPHY REST API.
 *
 * Owns every HTTP concern (base URL, API key, timeouts, error translation) so
 * the domain and application layers stay unaware of the provider.
 */
final readonly class GiphyGifRepository implements GifRepository
{
    public function __construct(
        private HttpClient $http,
        private GiphyGifMapper $mapper,
        private string $apiKey,
        private string $baseUrl,
        private int $timeout,
        private string $rating,
        private string $lang,
    ) {
    }

    public function search(SearchCriteria $criteria): GifSearchResult
    {
        $response = $this->send(fn (): Response => $this->client()->get('/gifs/search', [
            'api_key' => $this->apiKey,
            'q' => $criteria->query(),
            'limit' => $criteria->limit(),
            'offset' => $criteria->offset(),
            'rating' => $this->rating,
            'lang' => $this->lang,
        ]));

        if ($response->failed()) {
            throw GifProviderUnavailable::create();
        }

        return $this->mapper->toSearchResult((array) $response->json());
    }

    public function findById(GifId $id): Gif
    {
        $response = $this->send(fn (): Response => $this->client()->get('/gifs/'.$id->value(), [
            'api_key' => $this->apiKey,
        ]));

        if ($response->status() === 404) {
            throw GifNotFound::withId($id);
        }

        if ($response->failed()) {
            throw GifProviderUnavailable::create();
        }

        $data = $response->json('data');

        if (! is_array($data) || $data === []) {
            throw GifNotFound::withId($id);
        }

        return $this->mapper->toGif($data);
    }

    private function client(): PendingRequest
    {
        return $this->http
            ->baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson();
    }

    /**
     * Executes an HTTP call, translating transport failures into a domain error.
     *
     * @param callable(): Response $request
     */
    private function send(callable $request): Response
    {
        try {
            return $request();
        } catch (ConnectionException $exception) {
            throw GifProviderUnavailable::create($exception);
        }
    }
}
