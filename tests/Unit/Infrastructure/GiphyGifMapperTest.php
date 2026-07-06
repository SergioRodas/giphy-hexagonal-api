<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use Infrastructure\Giphy\GiphyGifMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GiphyGifMapperTest extends TestCase
{
    #[Test]
    public function it_maps_a_search_payload_to_the_domain_model(): void
    {
        $mapper = new GiphyGifMapper();

        $result = $mapper->toSearchResult([
            'data' => [
                [
                    'id' => 'Ev477g37MJORyOWfdG',
                    'title' => 'Cat Meme GIF',
                    'url' => 'https://giphy.com/gifs/Ev477g37MJORyOWfdG',
                    'rating' => 'g',
                    'username' => 'byomid',
                    'images' => [
                        'original' => ['url' => 'https://media.giphy.com/original.gif', 'width' => '480', 'height' => '476'],
                        'fixed_height_small' => ['url' => 'https://media.giphy.com/small.gif', 'width' => '100', 'height' => '99'],
                    ],
                ],
            ],
            'pagination' => ['total_count' => 500, 'count' => 1, 'offset' => 0],
        ]);

        $this->assertCount(1, $result->gifs());
        $gif = $result->gifs()[0];

        $this->assertSame('Ev477g37MJORyOWfdG', $gif->id()->value());
        $this->assertSame('Cat Meme GIF', $gif->title());
        $this->assertSame('byomid', $gif->username());
        $this->assertSame(480, $gif->original()->width());
        $this->assertSame('https://media.giphy.com/small.gif', $gif->preview()?->url());

        $this->assertSame(500, $result->pagination()->totalCount());
        $this->assertSame(0, $result->pagination()->offset());
    }

    #[Test]
    public function it_maps_a_missing_username_to_null(): void
    {
        $mapper = new GiphyGifMapper();

        $gif = $mapper->toGif([
            'id' => 'abc123',
            'title' => 'No user',
            'url' => 'https://giphy.com/gifs/abc123',
            'rating' => 'pg',
            'username' => '',
            'images' => ['original' => ['url' => 'https://media.giphy.com/o.gif', 'width' => '1', 'height' => '1']],
        ]);

        $this->assertNull($gif->username());
        $this->assertNull($gif->preview());
    }
}
