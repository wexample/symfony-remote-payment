<?php

namespace Wexample\SymfonyRemotePayment\Class;

use Wexample\SymfonyRemotePayment\Enum\ProviderPaymentStatus;

final readonly class ProviderRefund
{
    public function __construct(
        public string $refundReference,
        public string $paymentReference,
        public int $amount,
        public string $currencyCode,
        public ProviderPaymentStatus $status,
    ) {
    }
}
