<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent persistence model for a saved favorite GIF.
 *
 * @property int $id
 * @property int $user_id
 * @property string $gif_id
 * @property string $alias
 * @property \Illuminate\Support\Carbon $created_at
 */
class FavoriteModel extends Model
{
    protected $table = 'favorites';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'gif_id',
        'alias',
    ];
}
