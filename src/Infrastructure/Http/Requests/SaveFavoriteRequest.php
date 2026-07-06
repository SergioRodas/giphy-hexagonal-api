<?php

declare(strict_types=1);

namespace Infrastructure\Http\Requests;

use Domain\Favorite\ValueObject\Alias;
use Illuminate\Foundation\Http\FormRequest;

class SaveFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // GIPHY ids are alphanumeric (documented deviation from the brief's "numeric").
            'gif_id' => ['required', 'string', 'alpha_num', 'max:255'],
            'alias' => ['required', 'string', 'max:'.Alias::MAX_LENGTH],
            'user_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
