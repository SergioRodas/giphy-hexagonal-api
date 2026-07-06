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
        $userId = (int) $request->validated('user_id');

        // The brief accepts user_id as input, but a token holder may only manage
        // their own favorites: bind it to the authenticated principal (no IDOR).
        if ($userId !== (int) $request->user()->getAuthIdentifier()) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'You can only save favorites for your own account.',
            ], Response::HTTP_FORBIDDEN);
        }

        $favorite = $useCase->execute(new SaveFavoriteCommand(
            (string) $request->validated('gif_id'),
            (string) $request->validated('alias'),
            $userId,
        ));

        return FavoriteResource::make($favorite)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
