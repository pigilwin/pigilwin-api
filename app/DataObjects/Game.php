<?php

namespace App\DataObjects;

use Illuminate\Support\Collection;
use JsonSerializable;

readonly class Game implements JsonSerializable
{
    public function __construct(private int $gameId, private string $name){}

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->gameId,
            'name' => $this->name,
            'heroUrl' => "https://cdn.akamai.steamstatic.com/steam/apps/$this->gameId/library_hero.jpg",
            'logoUrl' => "https://cdn.cloudflare.steamstatic.com/steam/apps/$this->gameId/logo.png"
        ];
    }
}
