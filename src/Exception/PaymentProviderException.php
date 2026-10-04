<?php

namespace Wexample\SymfonyRemotePayment\Exception;

/**
 * A provider refused or failed a call. The message is safe to log, not to show.
 */
class PaymentProviderException extends \RuntimeException
{
}
