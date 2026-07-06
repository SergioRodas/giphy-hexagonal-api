<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Application\Gif\Search\SearchGifsQuery;
use Application\Gif\Search\SearchGifsUseCase;
use Application\Gif\Show\GetGifByIdQuery;
use Application\Gif\Show\GetGifByIdUseCase;
use Illuminate\Http\JsonResponse;
use Infrastructure\Http\Requests\SearchGifsRequest;
use Infrastructure\Http\Resources\GifResource;

final class GifController
{
    /**
     * GET /api/gifs/search — search GIFs by phrase/term.
     */
    public function search(SearchGifsRequest $request, SearchGifsUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(new SearchGifsQuery(
            (string) $request->validated('query'),
            $request->limitOrNull(),
            $request->offsetOrNull(),
        ));

        return GifResource::collection($result->gifs())
            ->additional(['pagination' => [
                'total_count' => $result->pagination()->totalCount(),
                'count' => $result->pagination()->count(),
                'offset' => $result->pagination()->offset(),
            ]])
            ->response();
    }

    /**
     * GET /api/gifs/{id} — fetch a single GIF by its identifier.
     */
    public function show(string $id, GetGifByIdUseCase $useCase): JsonResponse
    {
        $gif = $useCase->execute(new GetGifByIdQuery($id));

        return GifResource::make($gif)->response();
    }
}
