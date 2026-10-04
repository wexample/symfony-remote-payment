<?php

namespace Wexample\SymfonyRemotePayment\Interface;

/**
 * What every provider capability shares: a name, unique among providers ("stripe").
 */
interface PaymentProviderInterface
{
    public function getName(): string;
}
