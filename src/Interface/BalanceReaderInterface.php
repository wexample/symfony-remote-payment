<?php

namespace Wexample\SymfonyRemotePayment\Interface;

use DateTimeInterface;
use Wexample\SymfonyRemotePayment\Class\BalanceMovement;

/**
 * A provider whose merchant balance can be read, line by line, for bookkeeping.
 */
interface BalanceReaderInterface extends PaymentProviderInterface
{
    /**
     * Movements created in [from, to], oldest first.
     *
     * @return iterable<BalanceMovement>
     */
    public function readBalance(
        DateTimeInterface $from,
        ?DateTimeInterface $to = null
    ): iterable;
}
