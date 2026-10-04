<?php

namespace Wexample\SymfonyRemotePayment\Class;

use DateTimeImmutable;
use Wexample\SymfonyRemotePayment\Enum\BalanceMovementType;

/**
 * One line of the balance a provider keeps for the merchant (Stripe's balance
 * transactions). Accounting reads these to book what the bank never sees:
 * gross payments, fees, refunds, and payouts as transfers to the bank.
 *
 * Amounts are minor units. `amount` is signed (+ in, − out) and gross; `fee` is
 * what the provider kept, positive; `getNet()` is what reached the balance.
 */
final readonly class BalanceMovement
{
    public function __construct(
        public string $externalId,
        public BalanceMovementType $type,
        public int $amount,
        public int $fee,
        public string $currencyCode,
        public DateTimeImmutable $dateCreated,
        public ?DateTimeImmutable $dateAvailable = null,
        public ?string $description = null,
        public ?string $sourceReference = null,
        public ?string $paymentReference = null,
        public array $data = [],
    ) {
    }

    public function getNet(): int
    {
        return $this->amount - $this->fee;
    }
}
