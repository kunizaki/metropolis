# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Akeeba Web Push is a PHP library implementing the W3C Web Push Protocol for Joomla 4+ components. It allows Joomla extension developers to send encrypted browser push notifications to users. It is **not** a standalone Joomla component — it's a reusable library that components include via Composer or manual file copy.

**Derived from** Louis Lagrange's WebPush library, modified to use only Joomla-bundled dependencies.

## Build & Dependencies

- **PHP**: ^7.4 | ^8.0 (platform target: 7.4.999)
- **Required extensions**: `ext-openssl`, `ext-json`
- **Optional**: `ext-gmp` (better BigInteger performance)
- **Install**: `composer install`
- **No test suite, linter, or CI pipeline exists** in this repository.

## Architecture

### Namespace & Autoloading

PSR-4: `Akeeba\WebPush\` maps to `src/`.

### Core Subsystems

**`src/WebPush/`** — Core push notification engine:
- `WebPush.php` — Main sender class. Queues notifications and flushes them in batches (default 1000) via HTTP POST to browser push services.
- `VAPID.php` — VAPID key pair generation and JWT signing (ECDSA P-256).
- `Encryption.php` — AES-128-GCM / AES-GCM payload encryption with random salt and padding.
- `Subscription.php` / `SubscriptionInterface.php` — Browser subscription data (endpoint + p256dh + auth keys).
- `Notification.php` — Internal notification wrapper.
- `MessageSentReport.php` — Delivery result per notification.

**`src/ECC/`** — Custom Elliptic Curve Cryptography implementation for VAPID public key operations. Uses `Brick\Math\BigInteger` (Joomla-bundled).

**`src/Base64Url/`** — URL-safe Base64 encoding/decoding utility.

### Joomla Integration Layer (Trait-Based)

The library integrates into Joomla MVC via two traits:

- **`WebPushControllerTrait`** — Adds `webpushsubscribe()` and `webpushunsubscribe()` endpoints to any Joomla controller. Handles CSRF validation and JSON responses. Hook: `onAfterWebPushSaveSubscription()`.
- **`WebPushModelTrait`** — Adds `initialiseWebPush()`, `getVapidKeys()`, `sendNotification()`, and subscription CRUD to any Joomla model. Stores subscriptions as JSON in Joomla's `#__user_profiles` table (key: `{component}.webPushSubscription`). VAPID keys stored in component parameters.

**`NotificationOptions.php`** — Fluent DSO with ArrayAccess for notification display options.

### Data Flow

Browser subscribes via Service Worker → Controller saves subscription to `#__user_profiles` → Later, model calls `sendNotification()` → WebPush encrypts payload with AES-GCM + VAPID JWT → HTTP POST to push service → Browser receives and displays notification.

### Cryptography

Changes to VAPID or encryption code have breaking implications — the crypto stack (ECC, VAPID JWT signing, AES-GCM encryption) is tightly coupled and must remain compatible with the Web Push standard.

## Important Constraints

- **Plugin namespace conflict**: Cannot be used unmodified in Joomla plugins; namespace must be changed to avoid version conflicts between extensions.
- **Payload limits**: Max 4,078 bytes; compatibility limit 3,052 bytes.
- **Joomla version compat**: Handles Joomla 4/5/6 database API differences and PHP 8.1–8.5 reflection changes with runtime checks.
- **Static VAPID cache**: VAPID keys are cached in a static property per component to avoid repeated DB queries.
