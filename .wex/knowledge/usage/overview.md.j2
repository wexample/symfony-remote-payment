## Providers

Implementing any capability interface registers a service in `PaymentProviderRegistry`: `getGateway($name)`, `findGatewayForMethod('sepa_debit')`, `getWebhookParser($name)`, `getBalanceReaders()`.

The data exchanged is provider-neutral: `PaymentRequest` (refuses amounts ≤ 0), `PaymentInitiation`, `ProviderPayment`, `ProviderRefund`, `PaymentNotification`, and `BalanceMovement` — a line of the merchant balance with its gross amount, the fee kept, the payment it belongs to, so accounting can book what the bank never sees.

`FakePaymentProvider` offers every capability in memory, for tests and demos: `succeed()` and `fail()` return the notification a webhook would carry, and succeeded payments appear in its balance with a fee.
