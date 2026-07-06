<?php

declare(strict_types=1);

namespace Domain\Gif\Repository;

use Domain\Gif\Entity\Gif;
use Domain\Gif\Exception\GifNotFound;
use Domain\Gif\Exception\GifProviderUnavailable;
use Domain\Gif\GifSearchResult;
use Domain\Gif\ValueObject\GifId;
use Domain\Gif\ValueObject\SearchCriteria;

/**
 * Port to the external GIF provider (GIPHY). The infrastructure adapter is
 * responsible for the HTTP details and for mapping provider payloads to the
 * domain model.
 */
interface GifRepository
{
    /**
     * @throws GifProviderUnavailable when the provider cannot be reached.
     */
    public function search(SearchCriteria $criteria): GifSearchResult;

    /**
     * @throws GifNotFound when no GIF matches the given id.
     * @throws GifProviderUnavailable when the provider cannot be reached.
     */
    public function findById(GifId $id): Gif;
}
