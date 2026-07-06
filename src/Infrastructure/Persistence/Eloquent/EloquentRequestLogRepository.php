<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent;

use Domain\Audit\Entity\RequestLog;
use Domain\Audit\Repository\RequestLogRepository;
use Infrastructure\Persistence\Eloquent\Models\RequestLogModel;

final class EloquentRequestLogRepository implements RequestLogRepository
{
    public function save(RequestLog $log): void
    {
        RequestLogModel::query()->create([
            'user_id' => $log->userId()?->value(),
            'service' => $log->service(),
            'method' => $log->method(),
            'path' => $log->path(),
            'request_body' => $log->requestBody(),
            'status_code' => $log->statusCode(),
            'response_body' => $log->responseBody(),
            'ip_address' => $log->ipAddress(),
        ]);
    }
}
