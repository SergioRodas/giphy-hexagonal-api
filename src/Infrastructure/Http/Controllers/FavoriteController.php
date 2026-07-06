<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Application\Favorite\Save\SaveFavoriteCommand;
use Application\Favorite\Save\SaveFavoriteUseCase;
use Illuminate\Http\JsonResponse;
use Infrastructure\Http\Requests\SaveFavoriteRequest;
use Infrastructure\Http\Resources\FavoriteResource;
use Symfony\Component\HttpFoundation\Response;

final class FavoriteController
{
    /**
     * POST /api/favorites — store a favorite GIF for a user.
     */
    public function store(SaveFavoriteRequest $request, SaveFavoriteUseCase $useCase): JsonResponse
    {
        $favorite = $useCase->execute(new SaveFavoriteCommand(
            (string) $request->validated('gif_id'),
            (string) $request->validated('alias'),
            (int) $request->validated('user_id'),
        ));

        return FavoriteResource::make($favorite)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
