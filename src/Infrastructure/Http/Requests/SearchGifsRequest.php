<?php

declare(strict_types=1);

namespace Infrastructure\Http\Requests;

use Domain\Gif\ValueObject\SearchCriteria;
use Illuminate\Foundation\Http\FormRequest;

class SearchGifsRequest extends FormRequest
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
            'query' => ['required', 'string', 'max:200'],
            'limit' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:'.SearchCriteria::MAX_LIMIT],
            'offset' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    public function limitOrNull(): ?int
    {
        $limit = $this->validated('limit');

        return $limit === null ? null : (int) $limit;
    }

    public function offsetOrNull(): ?int
    {
        $offset = $this->validated('offset');

        return $offset === null ? null : (int) $offset;
    }
}
