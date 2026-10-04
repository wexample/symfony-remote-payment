<?php

namespace Wexample\SymfonyRemotePayment\Class;

use DateTimeImmutable;
use Wexample\SymfonyRemotePayment\Enum\ProviderPaymentStatus;

/**
 * A payment as the provider sees it now.
 */
final readonly class ProviderPayment
{
    public function __construct(
        public string $providerReference,
        public ProviderPaymentStatus $status,
        public int $amount,
        public string $currencyCode,
        public int $amountReceived = 0,
        public int $amountRefunded = 0,
        public ?string $chargeReference = null,
        public ?string $method = null,
        public ?DateTimeImmutable $datePaid = null,
        public ?string $failureMessage = null,
        public array $data = [],
    ) {
    }
}
