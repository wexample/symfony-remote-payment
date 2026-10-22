<?php

namespace Wexample\SymfonyRemotePayment\Enum;

/**
 * What moved the balance a provider holds for the merchant.
 */
enum BalanceMovementType: string
{
    /** A customer payment, gross. */
    case Charge = 'charge';

    /** Money given back to a customer. */
    case Refund = 'refund';

    /** Balance sent to the merchant's bank account. */
    case Payout = 'payout';

    /** A payout that came back. */
    case PayoutReversal = 'payout_reversal';

    /** A fee billed apart from a charge (subscription, Radar, …). */
    case Fee = 'fee';

    /** A disputed charge withdrawn, or its reversal. */
    case Dispute = 'dispute';

    case Adjustment = 'adjustment';

    case Other = 'other';
}
