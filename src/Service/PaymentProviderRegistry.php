<?php

namespace Wexample\SymfonyRemotePayment\Service;

use Wexample\SymfonyRemotePayment\Interface\BalanceReaderInterface;
use Wexample\SymfonyRemotePayment\Interface\PaymentGatewayInterface;
use Wexample\SymfonyRemotePayment\Interface\PaymentProviderInterface;
use Wexample\SymfonyRemotePayment\Interface\WebhookParserInterface;

/**
 * Every provider capability the application has, found by name or by method.
 * A provider may offer one capability only: a bank that only lets its account
 * be read is a BalanceReaderInterface and nothing else.
 */
class PaymentProviderRegistry
{
    /**
     * @param iterable<PaymentProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable $providers = [],
    ) {
    }

    public function getGateway(string $name): PaymentGatewayInterface
    {
        return $this->get($name, PaymentGatewayInterface::class);
    }

    public function findGatewayForMethod(string $method): ?PaymentGatewayInterface
    {
        foreach ($this->all(PaymentGatewayInterface::class) as $gateway) {
            if ($gateway->supports($method)) {
                return $gateway;
            }
        }

        return null;
    }

    public function getWebhookParser(string $name): WebhookParserInterface
    {
        return $this->get($name, WebhookParserInterface::class);
    }

    public function getBalanceReader(string $name): BalanceReaderInterface
    {
        return $this->get($name, BalanceReaderInterface::class);
    }

    /**
     * @return array<string, BalanceReaderInterface>
     */
    public function getBalanceReaders(): array
    {
        return $this->all(BalanceReaderInterface::class);
    }

    /**
     * @return array<string, PaymentGatewayInterface>
     */
    public function getGateways(): array
    {
        return $this->all(PaymentGatewayInterface::class);
    }

    public function has(
        string $name,
        string $capability = PaymentProviderInterface::class
    ): bool {
        return isset($this->all($capability)[$name]);
    }

    /**
     * @template T of PaymentProviderInterface
     * @param class-string<T> $capability
     * @return T
     */
    private function get(
        string $name,
        string $capability
    ): PaymentProviderInterface {
        $found = $this->all($capability)[$name] ?? null;

        if (null === $found) {
            throw new \InvalidArgumentException(sprintf('No payment provider "%s" offering %s.', $name, $capability));
        }

        return $found;
    }

    /**
     * @template T of PaymentProviderInterface
     * @param class-string<T> $capability
     * @return array<string, T>
     */
    private function all(string $capability): array
    {
        $found = [];

        foreach ($this->providers as $provider) {
            if ($provider instanceof $capability) {
                $found[$provider->getName()] = $provider;
            }
        }

        return $found;
    }
}
