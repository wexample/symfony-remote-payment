<?php

namespace Wexample\SymfonyRemotePayment\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyRemotePayment\Interface\PaymentProviderInterface;

class WexampleSymfonyRemotePaymentExtension extends AbstractWexampleSymfonyExtension
{
    public const string TAG_PROVIDER = 'wexample_symfony_remote_payment.provider';

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        // Implementing any capability is enough to be a provider.
        $container
            ->registerForAutoconfiguration(PaymentProviderInterface::class)
            ->addTag(self::TAG_PROVIDER);

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
