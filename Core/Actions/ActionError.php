<?php

declare(strict_types=1);

namespace Core\Actions;

use JsonSerializable;

class ActionError implements JsonSerializable
{
    public const NOT_FOUND = 'NOT_FOUND';

    public const BAD_REQUEST = 'BAD_REQUEST';

    public const NOT_ALLOWED = 'NOT_ALLOWED';

    public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';

    public const UNAUTHENTICATED = 'UNAUTHENTICATED';

    public const SERVER_ERROR = 'SERVER_ERROR';

    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';

    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';

    public const VALIDATION_ERROR = 'VALIDATION_ERROR';

    public const VERIFICATION_ERROR = 'VERIFICATION_ERROR';

    private string $type;

    private ?string $description;

    public function __construct(string $type, ?string $description = null)
    {
        $this->type = $type;
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'description' => $this->description,
        ];
    }
}
