<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObject;

final readonly class SourceTracking
{
    public function __construct(
        public ?string $utmSource = null,
        public ?string $utmMedium = null,
        public ?string $utmCampaign = null,
        public ?string $utmTerm = null,
        public ?string $utmContent = null,
        public ?string $referrer = null,
        public ?string $landingPage = null,
    ) {
    }

    public static function empty(): self
    {
        return new self();
    }

    public function toArray(): array
    {
        return [
            'utm_source' => $this->utmSource,
            'utm_medium' => $this->utmMedium,
            'utm_campaign' => $this->utmCampaign,
            'utm_term' => $this->utmTerm,
            'utm_content' => $this->utmContent,
            'referrer' => $this->referrer,
            'landing_page' => $this->landingPage,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            utmSource: $data['utm_source'] ?? null,
            utmMedium: $data['utm_medium'] ?? null,
            utmCampaign: $data['utm_campaign'] ?? null,
            utmTerm: $data['utm_term'] ?? null,
            utmContent: $data['utm_content'] ?? null,
            referrer: $data['referrer'] ?? null,
            landingPage: $data['landing_page'] ?? null,
        );
    }

    public function hasUtm(): bool
    {
        return null !== $this->utmSource || null !== $this->utmMedium || null !== $this->utmCampaign;
    }
}
