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
     *
     * The ownership rule (a token holder may only save favorites for their own
     * account) is enforced by the use case; this adapter only supplies the
     * authenticated principal alongside the validated input.
     */
    public function store(SaveFavoriteRequest $request, SaveFavoriteUseCase $useCase): JsonResponse
    {
        $favorite = $useCase->execute(new SaveFavoriteCommand(
            (string) $request->validated('gif_id'),
            (string) $request->validated('alias'),
            (int) $request->validated('user_id'),
            (int) $request->user()->getAuthIdentifier(),
        ));

        return FavoriteResource::make($favorite)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
