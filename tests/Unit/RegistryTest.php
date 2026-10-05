<?php

namespace Wexample\SymfonyRemotePayment\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Wexample\SymfonyRemotePayment\Class\FakePaymentProvider;
use Wexample\SymfonyRemotePayment\Class\PaymentRequest;
use Wexample\SymfonyRemotePayment\Enum\BalanceMovementType;
use Wexample\SymfonyRemotePayment\Enum\ProviderPaymentStatus;
use Wexample\SymfonyRemotePayment\Exception\InvalidWebhookException;
use Wexample\SymfonyRemotePayment\Interface\BalanceReaderInterface;
use Wexample\SymfonyRemotePayment\Service\PaymentProviderRegistry;

class RegistryTest extends TestCase
{
    public function testCapabilitiesAreFoundSeparately(): void
    {
        $readOnlyBank = new class () implements BalanceReaderInterface {
            public function getName(): string
            {
                return 'bank';
            }

            public function readBalance(\DateTimeInterface $from, ?\DateTimeInterface $to = null): iterable
            {
                return [];
            }
        };

        $registry = new PaymentProviderRegistry([new FakePaymentProvider('fake', ['card', 'sepa_debit']), $readOnlyBank]);

        $this->assertSame('fake', $registry->findGatewayForMethod('sepa_debit')?->getName());
        $this->assertNull($registry->findGatewayForMethod('cash'));
        $this->assertSame(['fake', 'bank'], array_keys($registry->getBalanceReaders()));
        $this->assertSame(['fake'], array_keys($registry->getGateways()));
        $this->assertFalse($registry->has('bank', \Wexample\SymfonyRemotePayment\Interface\PaymentGatewayInterface::class));

        $this->expectException(\InvalidArgumentException::class);
        $registry->getGateway('bank');
    }

    public function testZeroAmountIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PaymentRequest('p1', 0, 'EUR', 'card');
    }

    public function testFakeProviderFlow(): void
    {
        $provider = new FakePaymentProvider(feeRate: 150, feeFixed: 25);
        $initiation = $provider->initiate(new PaymentRequest('order-1', 10000, 'EUR', 'card'));

        $this->assertSame(ProviderPaymentStatus::Pending, $initiation->status);

        $notification = $provider->succeed($initiation->providerReference, new DateTimeImmutable('2026-01-10'));
        $this->assertSame('order-1', $notification->reference);
        $this->assertSame(ProviderPaymentStatus::Succeeded, $provider->fetch($initiation->providerReference)->status);

        $provider->refund($initiation->providerReference, 4000);
        $this->assertSame(ProviderPaymentStatus::PartiallyRefunded, $provider->fetch($initiation->providerReference)->status);

        $movements = iterator_to_array($provider->readBalance(new DateTimeImmutable('2026-01-01')), false);
        $this->assertSame(BalanceMovementType::Charge, $movements[0]->type);
        $this->assertSame(175, $movements[0]->fee);
        $this->assertSame(9825, $movements[0]->getNet());
        $this->assertSame(-4000, $movements[1]->amount);
    }

    public function testWebhookSignature(): void
    {
        $provider = new FakePaymentProvider();
        $initiation = $provider->initiate(new PaymentRequest('order-1', 500, 'EUR', 'card'));
        $payload = json_encode(['eventId' => 'e1', 'providerReference' => $initiation->providerReference, 'status' => 'succeeded']);

        $this->assertSame('order-1', $provider->parseWebhook($payload, ['x-fake-signature' => 'valid'])->reference);

        $this->expectException(InvalidWebhookException::class);
        $provider->parseWebhook($payload, []);
    }
}
