# symfony-remote-payment

Version: 3.0.6

Payment provider contracts: collect payments, read webhooks, read the merchant balance for bookkeeping

## Table of Contents

- [Introduction](#introduction)

`symfony-remote-payment` holds the contracts of payment providers, split by capability: collecting payments (`PaymentGatewayInterface`), reading their webhooks (`WebhookParserInterface`), reading the balance they keep for the merchant (`BalanceReaderInterface`). A provider implements what it offers — Stripe all three, a bank whose account can only be read the last one — in its own `symfony-remote-payment-<provider>` package. `symfony-payment` and `symfony-accounting` use the contracts and never a provider.
