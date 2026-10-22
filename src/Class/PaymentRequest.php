<?php

namespace Wexample\SymfonyRemotePayment\Class;

/**
 * What the application asks a provider to collect.
 */
final readonly class PaymentRequest
{
    /**
     * @param string $reference The application's id for the payment, sent back in notifications.
     * @param int $amount Minor units, strictly positive.
     * @param array<string, scalar> $metadata Stored by the provider next to the payment.
     */
    public function __construct(
        public string $reference,
        public int $amount,
        public string $currencyCode,
        public string $method,
        public ?string $description = null,
        public ?string $customerEmail = null,
        public ?string $returnUrl = null,
        public array $metadata = [],
        public ?string $idempotencyKey = null,
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('A payment request needs a strictly positive amount; complete zero amounts without a provider.');
        }
    }
}
