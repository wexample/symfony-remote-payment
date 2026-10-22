<?php

namespace Wexample\SymfonyRemotePayment\Interface;

/**
 * A provider whose payments are confirmed by the payer in the browser, on the
 * application's own page, with what the initiation gave back — a client
 * secret: a card typed in the provider's own form, a bank's check.
 *
 * Which component draws that form, and with what, is the provider's to say;
 * the page showing it knows no provider by name.
 */
interface BrowserConfirmationInterface extends PaymentProviderInterface
{
    /**
     * The loader component drawing the provider's form
     * (`@<Bundle>/components/<name>`).
     */
    public function getBrowserConfirmationComponent(): string;

    /**
     * What it is drawn with, beside the client secret and the address to
     * come back to, which the page adds: a public key.
     *
     * @return array<string, mixed>
     */
    public function getBrowserConfirmationOptions(): array;
}
