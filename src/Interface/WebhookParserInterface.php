<?php

namespace Wexample\SymfonyRemotePayment\Interface;

use Wexample\SymfonyRemotePayment\Class\PaymentNotification;
use Wexample\SymfonyRemotePayment\Exception\InvalidWebhookException;

/**
 * A provider notifying payment changes by webhook. Payment state is driven by
 * these, never by the browser.
 */
interface WebhookParserInterface extends PaymentProviderInterface
{
    /**
     * Verifies the signature and reduces the event to a notification.
     * Returns null for events that do not concern a payment status.
     *
     * @param array<string, string> $headers Lower-cased header names.
     *
     * @throws InvalidWebhookException When the signature does not verify.
     */
    public function parseWebhook(
        string $payload,
        array $headers
    ): ?PaymentNotification;
}
