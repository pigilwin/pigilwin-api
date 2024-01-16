<?php

namespace App\DataObjects;

use JsonSerializable;

readonly class Achievement implements JsonSerializable
{

    public function __construct(
        private string $id,
        private string $name,
        private string $description,
        private bool $completed,
        private int $unlockTime,
        private string $iconUrl,
        private string $grayIconUrl,
        private bool $hidden,
    ){}

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'completed' => $this->completed,
            'unlockTime' => $this->unlockTime,
            'iconUrl' => $this->iconUrl,
            'grayIconUrl' => $this->grayIconUrl,
            'hidden' => $this->hidden
        ];
    }
}
