<?php

namespace Wexample\SymfonyRemotePayment\Class;

use Wexample\SymfonyRemotePayment\Enum\ProviderPaymentStatus;

/**
 * A verified webhook, reduced to what changes a payment.
 */
final readonly class PaymentNotification
{
    /**
     * @param string $eventId Unique per event, to ignore replays.
     */
    public function __construct(
        public string $provider,
        public string $eventId,
        public string $providerReference,
        public ProviderPaymentStatus $status,
        public ?int $amountReceived = null,
        public ?string $chargeReference = null,
        public ?string $failureMessage = null,
        public ?string $reference = null,
        public array $data = [],
    ) {
    }
}
