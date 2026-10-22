<?php

namespace Wexample\SymfonyRemotePayment\Class;

use DateTimeImmutable;
use DateTimeInterface;
use Wexample\SymfonyRemotePayment\Enum\BalanceMovementType;
use Wexample\SymfonyRemotePayment\Enum\ProviderPaymentStatus;
use Wexample\SymfonyRemotePayment\Exception\InvalidWebhookException;
use Wexample\SymfonyRemotePayment\Interface\BalanceReaderInterface;
use Wexample\SymfonyRemotePayment\Interface\PaymentGatewayInterface;
use Wexample\SymfonyRemotePayment\Interface\WebhookParserInterface;

/**
 * An in-memory provider offering every capability, for tests and demos.
 *
 * Payments start pending; tests move them with succeed()/fail(), which also
 * return the matching notification as a provider webhook would. Succeeded
 * payments appear in the balance, with the configured fee.
 */
class FakePaymentProvider implements PaymentGatewayInterface, WebhookParserInterface, BalanceReaderInterface
{
    /** @var array<string, ProviderPayment> */
    private array $payments = [];

    /** @var array<string, string> Provider reference → application reference */
    private array $references = [];

    /** @var list<BalanceMovement> */
    private array $movements = [];

    /** @var list<PaymentRequest> */
    public array $requests = [];

    private int $sequence = 0;

    /**
     * @param list<string> $methods
     */
    public function __construct(
        private readonly string $name = 'fake',
        private readonly array $methods = ['card'],
        private readonly int $feeRate = 150,
        private readonly int $feeFixed = 25,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function supports(string $method): bool
    {
        return in_array($method, $this->methods, true);
    }

    public function initiate(PaymentRequest $request): PaymentInitiation
    {
        $this->requests[] = $request;
        $reference = $this->name.'_pay_'.++$this->sequence;
        $this->references[$reference] = $request->reference;
        $this->payments[$reference] = new ProviderPayment(
            providerReference: $reference,
            status: ProviderPaymentStatus::Pending,
            amount: $request->amount,
            currencyCode: $request->currencyCode,
            method: $request->method,
        );

        return new PaymentInitiation($reference, ProviderPaymentStatus::Pending, clientSecret: $reference.'_secret');
    }

    public function fetch(string $providerReference): ProviderPayment
    {
        return $this->payments[$providerReference]
            ?? throw new \InvalidArgumentException('Unknown payment '.$providerReference);
    }

    public function cancel(string $providerReference): ProviderPayment
    {
        return $this->update($providerReference, ProviderPaymentStatus::Canceled);
    }

    public function refund(
        string $providerReference,
        ?int $amount = null,
        ?string $reason = null
    ): ProviderRefund {
        $payment = $this->fetch($providerReference);
        $amount ??= $payment->amountReceived - $payment->amountRefunded;
        $refunded = $payment->amountRefunded + $amount;
        $status = $refunded >= $payment->amountReceived
            ? ProviderPaymentStatus::Refunded
            : ProviderPaymentStatus::PartiallyRefunded;

        $this->payments[$providerReference] = new ProviderPayment(
            $providerReference,
            $status,
            $payment->amount,
            $payment->currencyCode,
            $payment->amountReceived,
            $refunded,
            $payment->chargeReference,
            $payment->method,
            $payment->datePaid,
        );

        $refundReference = $this->name.'_re_'.++$this->sequence;
        $this->movements[] = new BalanceMovement(
            externalId: $this->name.'_txn_'.++$this->sequence,
            type: BalanceMovementType::Refund,
            amount: -$amount,
            fee: 0,
            currencyCode: $payment->currencyCode,
            dateCreated: new DateTimeImmutable(),
            description: 'Refund '.$providerReference,
            sourceReference: $refundReference,
            paymentReference: $providerReference,
        );

        return new ProviderRefund($refundReference, $providerReference, $amount, $payment->currencyCode, $status);
    }

    public function succeed(
        string $providerReference,
        ?DateTimeImmutable $date = null
    ): PaymentNotification {
        $payment = $this->fetch($providerReference);
        $date ??= new DateTimeImmutable();
        $charge = $this->name.'_ch_'.++$this->sequence;

        $this->payments[$providerReference] = new ProviderPayment(
            $providerReference,
            ProviderPaymentStatus::Succeeded,
            $payment->amount,
            $payment->currencyCode,
            $payment->amount,
            0,
            $charge,
            $payment->method,
            $date,
        );

        $this->movements[] = new BalanceMovement(
            externalId: $this->name.'_txn_'.++$this->sequence,
            type: BalanceMovementType::Charge,
            amount: $payment->amount,
            fee: $this->calcFee($payment->amount),
            currencyCode: $payment->currencyCode,
            dateCreated: $date,
            description: 'Payment '.$this->references[$providerReference],
            sourceReference: $charge,
            paymentReference: $providerReference,
        );

        return $this->notification($providerReference, ProviderPaymentStatus::Succeeded, $payment->amount, $charge);
    }

    public function fail(
        string $providerReference,
        string $message = 'Card declined.'
    ): PaymentNotification {
        $this->update($providerReference, ProviderPaymentStatus::Failed, $message);

        return $this->notification($providerReference, ProviderPaymentStatus::Failed, failureMessage: $message);
    }

    /**
     * Sends the available balance to the bank, as a payout movement.
     */
    public function payout(?DateTimeImmutable $date = null): BalanceMovement
    {
        $net = 0;
        foreach ($this->movements as $movement) {
            $net += $movement->getNet();
        }

        return $this->movements[] = new BalanceMovement(
            externalId: $this->name.'_txn_'.++$this->sequence,
            type: BalanceMovementType::Payout,
            amount: -$net,
            fee: 0,
            currencyCode: 'EUR',
            dateCreated: $date ?? new DateTimeImmutable(),
            description: 'Payout',
            sourceReference: $this->name.'_po_'.$this->sequence,
        );
    }

    public function addMovement(BalanceMovement $movement): void
    {
        $this->movements[] = $movement;
    }

    /**
     * A fake webhook payload is the JSON of a notification, "signed" with the header
     * `x-fake-signature: valid`.
     */
    public function parseWebhook(
        string $payload,
        array $headers
    ): ?PaymentNotification {
        if ('valid' !== ($headers['x-fake-signature'] ?? null)) {
            throw new InvalidWebhookException('Bad signature.');
        }

        $data = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);

        return new PaymentNotification(
            provider: $this->name,
            eventId: $data['eventId'],
            providerReference: $data['providerReference'],
            status: ProviderPaymentStatus::from($data['status']),
            amountReceived: $data['amountReceived'] ?? null,
            reference: $this->references[$data['providerReference']] ?? null,
        );
    }

    public function readBalance(
        DateTimeInterface $from,
        ?DateTimeInterface $to = null
    ): iterable {
        foreach ($this->movements as $movement) {
            if ($movement->dateCreated >= $from && (null === $to || $movement->dateCreated <= $to)) {
                yield $movement;
            }
        }
    }

    public function calcFee(int $amount): int
    {
        return intdiv($amount * $this->feeRate + 5000, 10000) + $this->feeFixed;
    }

    private function update(
        string $providerReference,
        ProviderPaymentStatus $status,
        ?string $failureMessage = null
    ): ProviderPayment {
        $payment = $this->fetch($providerReference);

        return $this->payments[$providerReference] = new ProviderPayment(
            $providerReference,
            $status,
            $payment->amount,
            $payment->currencyCode,
            $payment->amountReceived,
            $payment->amountRefunded,
            $payment->chargeReference,
            $payment->method,
            $payment->datePaid,
            $failureMessage,
        );
    }

    private function notification(
        string $providerReference,
        ProviderPaymentStatus $status,
        ?int $amountReceived = null,
        ?string $charge = null,
        ?string $failureMessage = null
    ): PaymentNotification {
        return new PaymentNotification(
            provider: $this->name,
            eventId: $this->name.'_evt_'.++$this->sequence,
            providerReference: $providerReference,
            status: $status,
            amountReceived: $amountReceived,
            chargeReference: $charge,
            failureMessage: $failureMessage,
            reference: $this->references[$providerReference] ?? null,
        );
    }
}
