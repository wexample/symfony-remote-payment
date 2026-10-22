<?php

namespace Wexample\SymfonyRemotePayment\Interface;

use Wexample\SymfonyRemotePayment\Class\PaymentInitiation;
use Wexample\SymfonyRemotePayment\Class\PaymentRequest;
use Wexample\SymfonyRemotePayment\Class\ProviderPayment;
use Wexample\SymfonyRemotePayment\Class\ProviderRefund;

/**
 * A provider able to collect payments.
 */
interface PaymentGatewayInterface extends PaymentProviderInterface
{
    /**
     * Whether this gateway handles a method ("card", "sepa_debit", "transfer"…).
     */
    public function supports(string $method): bool;

    public function initiate(PaymentRequest $request): PaymentInitiation;

    public function fetch(string $providerReference): ProviderPayment;

    public function cancel(string $providerReference): ProviderPayment;

    /**
     * @param int|null $amount Minor units; null refunds what is left.
     */
    public function refund(
        string $providerReference,
        ?int $amount = null,
        ?string $reason = null
    ): ProviderRefund;
}
