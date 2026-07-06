<?php

declare(strict_types=1);

namespace Domain\Audit\Repository;

use Domain\Audit\Entity\RequestLog;

/**
 * Port to the interaction-log persistence store.
 */
interface RequestLogRepository
{
    public function save(RequestLog $log): void;
}
