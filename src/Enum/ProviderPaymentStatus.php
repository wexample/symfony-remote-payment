<?php

namespace Wexample\SymfonyRemotePayment\Enum;

/**
 * The state of a payment as the provider reports it.
 */
enum ProviderPaymentStatus: string
{
    /** Created, waiting for the payer (card form, transfer to send). */
    case Pending = 'pending';

    /** The payer must act: 3-D Secure, redirect. */
    case RequiresAction = 'requires_action';

    /** Accepted, money not settled yet (SEPA debit). */
    case Processing = 'processing';

    case Succeeded = 'succeeded';

    case Failed = 'failed';

    case Canceled = 'canceled';

    case Refunded = 'refunded';

    case PartiallyRefunded = 'partially_refunded';

    public function isFinal(): bool
    {
        return in_array($this, [self::Succeeded, self::Failed, self::Canceled, self::Refunded], true);
    }
}
