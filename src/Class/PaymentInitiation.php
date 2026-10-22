<?php

namespace Wexample\SymfonyRemotePayment\Class;

use Wexample\SymfonyRemotePayment\Enum\ProviderPaymentStatus;

/**
 * The answer of a provider to a payment request: its own reference, and what the
 * browser needs to go on (a client secret for an embedded form, or a redirect).
 */
final readonly class PaymentInitiation
{
    /**
     * @param array<string, mixed> $data Provider-specific values worth keeping.
     */
    public function __construct(
        public string $providerReference,
        public ProviderPaymentStatus $status,
        public ?string $clientSecret = null,
        public ?string $redirectUrl = null,
        public array $data = [],
    ) {
    }
}
