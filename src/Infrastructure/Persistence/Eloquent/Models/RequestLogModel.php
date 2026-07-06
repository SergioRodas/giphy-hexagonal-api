<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent persistence model for the interaction audit log.
 *
 * Audit rows are immutable once written, hence only created_at is tracked.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $service
 * @property array<string, mixed>|null $request_body
 * @property int $status_code
 * @property array<string, mixed>|null $response_body
 * @property string|null $ip_address
 */
class RequestLogModel extends Model
{
    public const ?string UPDATED_AT = null;

    protected $table = 'request_logs';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'service',
        'method',
        'path',
        'request_body',
        'status_code',
        'response_body',
        'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_body' => 'array',
            'response_body' => 'array',
            'status_code' => 'integer',
        ];
    }
}
