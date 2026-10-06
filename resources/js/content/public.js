// Single source of truth for public-site copy.
//
// Values marked as REAL are taken directly from the application source
// (license lifecycle, API routes, plan limits, offline signing). Anything that
// cannot be verified from the codebase is intentionally marked "[placeholder]"
// so the site never states unverifiable facts as if they were real.

export const site = {
    name: 'CoreVisys',
    product: 'Corevisys Pro',
    tagline: 'Software licensing you can verify — online and offline.',
    description:
        'CoreVisys issues, activates, and verifies software licenses. Activate against a domain, bind to an environment fingerprint, and verify signatures offline with an RSA-SHA256 public key.',
};

export const contact = {
    // Emails were present across the site; kept as-is.
    salesEmail: 'hello@corevisys.com',
    supportEmail: 'support@corevisys.com',
    legalEmail: 'legal@corevisys.com',
    privacyEmail: 'privacy@corevisys.com',
    // Not verifiable in the codebase -> placeholders.
    phone: '[placeholder]',
    address: '[placeholder]',
    supportHours: '[placeholder]',
    responseTime: '[placeholder]',
    refundPolicy: '[placeholder]',
    sla: '[placeholder]',
};

// REAL: matches the named routes in routes/web.php
export const nav = [
    { label: 'Home', route: 'home' },
    { label: 'Pricing', route: 'pricing' },
    { label: 'Developers', route: 'developers' },
    { label: 'Contact', route: 'contact' },
];

// REAL: derived from app/Services/LicenseService.php, LicenseController.php and config/services.php
export const lifecycle = [
    {
        title: 'Choose a plan',
        body: 'Pick a product and plan on the Pricing page. Checkout runs with an online gateway when one is enabled, or as an offline bank transfer that an administrator verifies.',
    },
    {
        title: 'License issued',
        body: 'Once payment is verified the order is fulfilled and a license key is generated with the [PREFIX]-XXXX-XXXX-XXXX format. Keys are stored hashed (SHA-256 + per-license salt), never in plain text.',
    },
    {
        title: 'Activate',
        body: 'Your application calls POST /api/v1/license/activate with the license key, its domain, and its IP. The license binds to the first domain that activates it, and optionally to a machine fingerprint.',
    },
    {
        title: 'Stay in sync',
        body: 'Send a lightweight POST /api/v1/license/pulse heartbeat approximately once a month to keep last_check_at current. Use POST /api/v1/license/check for a read-only status check with no side effects.',
    },
    {
        title: 'Expiry & grace',
        body: 'Reminders are sent 7, 3, and 1 day before expiry, by email or your chosen notification channels. After the expiry date the license keeps working during a 7-day grace window before it is treated as expired.',
    },
    {
        title: 'Renew or reset',
        body: 'Subscriptions renew automatically when auto-renew is on; otherwise you renew from your dashboard. If a machine or domain changes, a license reset is an administrator action (recorded with a reason) that you request through support.',
    },
];

// REAL: activation behaviour from LicenseService and LicenseController
export const activationFacts = [
    { label: 'Trial length', value: '180 days (default)' },
    { label: 'Grace period', value: '7 days after expiry' },
    { label: 'Expiry reminders', value: '7, 3, and 1 day before' },
    { label: 'Key format', value: '[PREFIX]-XXXX-XXXX-XXXX' },
    { label: 'Key storage', value: 'SHA-256 hash + per-license salt' },
    { label: 'Domain binding', value: 'First domain (TOFU)' },
    { label: 'Activation limit', value: 'Set per license' },
    { label: 'Machine fingerprint', value: 'Optional, per license' },
    { label: 'Offline validity window', value: '7 days from last check (configurable)' },
    { label: 'Offline signing', value: 'RSA-SHA256 signature' },
];

// REAL: license enforcement facts from LicenseService, License and config/services.php
// Used by the product page "How this license works" notes block.
export const licenseNotes = [
    {
        title: 'Domain lock',
        body: 'A license activates against the first domain that uses it. The same key on a different domain is rejected unless an administrator resets the binding.',
    },
    {
        title: 'Device / fingerprint lock',
        body: 'An optional machine fingerprint can be bound to the license. Enforcement modes (standard, strict, active) decide how strictly a mismatch is treated.',
    },
    {
        title: 'Grace period',
        body: 'After the expiry date the license keeps working for a 7-day grace window before it is treated as expired. A failed recurring charge also starts a 7-day grace period.',
    },
    {
        title: 'Offline license',
        body: 'Activation and heartbeat responses are signed with RSA-SHA256, so the app can verify a license for up to 7 days without reaching the network.',
    },
    {
        title: 'Reset',
        body: 'A reset clears the domain and fingerprint binding. It is an administrator action, recorded with a reason, and requested through support.',
    },
    {
        title: 'Trial',
        body: 'A trial runs for 180 days by default, or for the billing period set on that product\u2019s trial price. Trials are limited to one per customer and environment.',
    },
];

// REAL: checkout behaviour from the order handler and callbacks in routes/web.php
export const paymentFlow = [
    {
        title: 'Online gateway',
        body: 'Card and mobile-wallet checkouts redirect to the provider, then return to the store. The order is fulfilled as soon as the provider confirms payment.',
    },
    {
        title: 'Offline bank transfer',
        body: 'Choose offline checkout, pay by bank transfer, then submit the transfer details so an administrator can verify them.',
    },
    {
        title: 'Manual verification',
        body: 'An administrator verifies the transfer. Typical approval time is [placeholder].',
    },
    {
        title: 'License issued',
        body: 'Once payment is verified the order is fulfilled and the license is issued as [PREFIX]-XXXX-XXXX-XXXX, stored hashed (SHA-256 + per-license salt).',
    },
];

// Shown on a product page when the product has no stored feature list.
export const productFeaturesPlaceholder = '[Add product features]';

// Copy for the per-product page at /pricing/{slug}. Facts mirror the license
// model above; anything the codebase cannot confirm stays a marked placeholder.
export const productPage = {
    breadcrumbPricing: 'Pricing',
    startingFrom: 'Starting from',
    buyLabel: 'Buy this product',
    trialLabel: 'Start free trial',
    trialNote:
        'A trial runs for 180 days by default, or for the billing period set on this product\u2019s trial price. Trials are limited to one per customer and environment.',
    askTrialLabel: 'Ask about a trial',
    checkoutNote:
        'Checkout continues in the store with the plan you choose. Online gateways appear when enabled; offline bank transfer with manual verification is always available.',
    featuresHeading: 'What you get',
    featuresEmptyNote:
        'This product does not store a structured feature list yet, so none is invented here. The licence model below applies to every product.',
    licenceHeading: 'How this license works',
    notesHeading: 'License notes',
    paymentHeading: 'Payment and what happens after paying',
    faqHeading: 'Product questions',
    otherHeading: 'Other products',
    otherNote: 'Browse the rest of the catalogue, or compare every plan side by side.',
    otherLink: 'See all pricing',
    plansHeading: 'Plans and billing cycles',
    oneTime: 'One-time',
    yearly: 'Yearly',
    monthly: 'Monthly',
    planColumn: 'Plan',
    cycleColumn: 'Billing cycle',
    amountColumn: 'Amount',
    plansEmptyNote: 'No plans are published for this product yet ([placeholder]).',
};

// Product-page FAQ. Real answers only; unverified details are marked.
export const productFaqs = [
    {
        q: 'Can I use this license on more than one domain?',
        a: 'A license activates against the first domain that uses it. Optional machine-fingerprint binding can tie it to a specific environment as well. Moving a license to a different domain or machine requires a reset, which is an administrator action requested through support.',
    },
    {
        q: 'What happens if the license expires?',
        a: 'A reminder is sent 7, 3, and 1 day before expiry. After the expiry date the license keeps working for a 7-day grace window, then it is treated as expired until it is renewed.',
    },
    {
        q: 'Can the software verify a license without internet access?',
        a: 'Yes. Activation and heartbeat responses are signed with RSA-SHA256, so the application can verify a license offline for typically 7 days (configurable) using the published public key.',
    },
    {
        q: 'How is the license delivered after payment?',
        a: 'Once an online gateway confirms payment, or an administrator verifies an offline bank transfer, the order is fulfilled and the license is issued in the [PREFIX]-XXXX-XXXX-XXXX format. Keys are stored hashed, so only a short reference (the last four characters) is shown in the dashboard afterwards.',
    },
    {
        q: 'What is the refund policy?',
        a: 'Refunds are not defined in the application, so no refund terms are published here ([placeholder]). Contact support with your order reference and we will review it.',
    },
];

// Footer line for the product-page payment section.
export const productPaymentNote =
    'Typical manual verification time is [placeholder]; the application does not define an approval-time setting.';

// REAL: enforcement modes are validated in LicenseController
export const enforcementModes = [
    {
        name: 'standard',
        summary: 'Default mode.',
        detail: 'The license is validated against its domain and status. Fingerprint mismatches are tolerated while the fingerprint grace window is open.',
    },
    {
        name: 'strict',
        summary: 'Fingerprint required.',
        detail: 'A matching environment fingerprint is required. Requests without a matching fingerprint are rejected.',
    },
    {
        name: 'active',
        summary: 'Enforced binding.',
        detail: 'Behaves like strict fingerprint enforcement for machines that are already bound, for maximum control in managed environments.',
    },
];

// REAL: pricing badges and capability names mirror the license model
export const pricingHighlights = [
    {
        title: 'Domain activation',
        body: 'A license activates against the domain it is first used on, protecting against key sharing.',
    },
    {
        title: 'Fingerprint binding',
        body: 'Bind a license to a machine fingerprint for tighter control, with a grace window to avoid lock-outs during migrations.',
    },
    {
        title: 'Offline verification',
        body: 'Responses are signed with RSA-SHA256 so your app can verify authenticity without a network round-trip.',
    },
    {
        title: 'Automatic reminders',
        body: 'Customers get notified 7, 3, and 1 day before a license expires, plus a 7-day grace window.',
    },
];

// REAL: API surface from routes/api.php
export const apiEndpoints = [
    {
        method: 'POST',
        path: '/api/v1/license/activate',
        auth: 'Public (throttled)',
        summary: 'Activate a license against a domain and IP. Binds the license on first activation.',
    },
    {
        method: 'POST',
        path: '/api/v1/license/check',
        auth: 'Public (throttled)',
        summary: 'Read-only validity check. Performs no side effects — no activation rows, no binding changes.',
    },
    {
        method: 'POST',
        path: '/api/v1/license/pulse',
        auth: 'Public (throttled)',
        summary: 'Lightweight heartbeat that updates last_check_at and reports current status.',
    },
    {
        method: 'GET',
        path: '/api/v1/license/public-key',
        auth: 'Public',
        summary: 'Returns the active signing key, algorithm, and key rotation metadata.',
    },
    {
        method: 'POST',
        path: '/api/v1/license/history',
        auth: 'Bearer token',
        summary: 'Returns activation history for a license.',
    },
    {
        method: 'GET',
        path: '/api/v1/products',
        auth: 'Public',
        summary: 'Lists available products and their prices.',
    },
];

// REAL: request/response shapes from LicenseController
export const activationExample = {
    request: `curl -X POST https://your-corevisys-host/api/v1/license/activate \\
  -H "Content-Type: application/json" \\
  -d '{
    "license_key": "[PREFIX]-XXXX-XXXX-XXXX",
    "domain": "app.example.com",
    "ip": "203.0.113.10",
    "fingerprint": "optional-machine-fingerprint",
    "enforcement_mode": "standard"
  }'`,
    response: `{
  "status": "success",
  "data": {
    "license_status": "active",
    "license_type": "full",
    "expires_at": "2027-03-01T00:00:00+00:00",
    "issued_at": "2026-03-01T00:00:00+00:00",
    "offline_valid_until": "2026-03-08T00:00:00+00:00"
  },
  "payload": "<base64 canonical payload>",
  "server_signature": "<base64 RSA-SHA256 signature>",
  "key_id": "corevisys-key-1",
  "algorithm": "RSA-SHA256"
}`,
};

export const offlineExample = `# Verify an offline response with the public key
openssl dgst -sha256 -verify public_key.pem \\
  -signature <(echo "<base64 server_signature>" | base64 -d) \\
  <(echo "<base64 payload>" | base64 -d)`;

// REAL: values from config/services.php and OfflineLicenseVerification
export const offlineFacts = [
    { label: 'Algorithm', value: 'RSA-SHA256' },
    { label: 'Default key id', value: 'corevisys-key-1' },
    { label: 'Rotation overlap', value: '30 days' },
    { label: 'Revoked keys', value: 'Rejected on verify' },
];

export const offlineSteps = [
    {
        title: 'Read the public key',
        body: 'Call GET /api/v1/license/public-key. It returns the active key id, the public key, the algorithm, and any rotation metadata your client needs.',
    },
    {
        title: 'Cache the signed payload',
        body: 'Activation and pulse responses include a base64 payload plus a base64 server_signature. Store both so the license can be verified while offline.',
    },
    {
        title: 'Verify locally',
        body: 'Canonicalise the payload (keys sorted, JSON without extra whitespace) and verify the RSA-SHA256 signature against the cached public key.',
    },
    {
        title: 'Respect the window',
        body: 'The signed payload includes an offline_valid_until timestamp. Your application can operate without contacting the server until that date, typically 7 days from the last successful check or pulse. Suspended pulse responses intentionally omit offline_valid_until; clients must treat a missing offline_valid_until as no offline use.',
    },
    {
        title: 'Fail closed',
        body: 'If the signing key is unavailable on the server, the API returns 503 rather than an unsigned response.',
    },
];

export const developerNotes = [
    'Signing keys are read through config(), never directly from the environment in application code.',
    'Revoked key ids are rejected during verification, so key rotation does not break already-issued licenses.',
    'In check and pulse, localhost and 127.0.0.1 are treated as the same host.',
];

// REAL: from the license/order model
export const comparisonRows = [
    { feature: 'Domain binding', basic: 'First activation', pro: 'First activation', enterprise: 'First activation' },
    { feature: 'Environment fingerprint', basic: 'Optional', pro: 'Optional', enterprise: 'Optional / enforced' },
    { feature: 'Offline signed verification', basic: 'Included', pro: 'Included', enterprise: 'Included' },
    { feature: 'Expiry reminders', basic: '7 / 3 / 1 days', pro: '7 / 3 / 1 days', enterprise: '7 / 3 / 1 days' },
    { feature: 'Grace period', basic: '7 days', pro: '7 days', enterprise: '7 days' },
    { feature: 'Activation limit', basic: 'Set per license', pro: 'Set per license', enterprise: 'Set per license' },
];

export const faqs = [
    {
        q: 'What happens when a license expires?',
        a: 'A reminder is sent 7, 3, and 1 day before the expiry date. Once it passes, the license continues to work during a 7-day grace window, then it is treated as expired until renewed.',
    },
    {
        q: 'Can I move a license to a new domain or machine?',
        a: 'A license binds to the first domain that activates it. Moving it requires a license reset — an administrator action that clears the binding, is recorded against the license with a reason, and is requested through support rather than from your dashboard.',
    },
    {
        q: 'How does offline verification work?',
        a: 'API responses carry a canonicalised payload and an RSA-SHA256 signature. Your application caches the public key and the signed response, then verifies the signature locally. The offline_valid_until field in the response tells you exactly how long the cached response is trusted — typically 7 days.',
    },
    {
        q: 'Which payment methods are supported?',
        a: 'Online card and mobile-wallet gateways can be enabled per deployment. Offline payment (bank transfer, verified by an administrator) is always available. Check the Pricing page to see which gateways are currently switched on.',
    },
    {
        q: 'Are activation attempts rate limited?',
        a: 'Yes. Activation and check requests use a strict per-license throttle. The pulse heartbeat endpoint allows up to 5 calls per hour per license, so occasional monthly check-ins stay well within limits.',
    },
    {
        q: 'How do I get support?',
        a: `Email ${contact.supportEmail}. Support hours, response times, and any service-level commitments are ${contact.sla === '[placeholder]' ? 'not published yet ([placeholder])' : contact.sla}.`,
    },
    {
        q: 'Do licenses renew automatically?',
        a: 'Subscription licenses carry auto-renew. The scheduler charges the saved method when the billing date is due and extends the license on success; if the charge fails, a 7-day grace period starts. One-time licenses do not renew automatically — you renew them from your dashboard when you choose.',
    },
    {
        q: 'How many domains or devices can one license use?',
        a: 'Each license carries an activation limit that is set when it is issued, and it binds to the first domain that activates it. Optional machine-fingerprint binding adds a second layer of control. The limit is per license, so it can differ between products and plans.',
    },
    {
        q: 'Can I get a refund?',
        a: 'Refunds are not defined in the application, so no refund terms are published here ([placeholder]). Contact support with your order reference and we will review it.',
    },
];

export const legal = {
    privacy: {
        title: 'Privacy Policy',
        updated: '[placeholder]',
        intro:
            'This policy explains what CoreVisys collects when you use the licensing platform, why it is collected, and the choices available to you.',
        sections: [
            {
                heading: 'Information we collect',
                body: 'Account details (name, email), license and order records, activation metadata such as the requesting domain, IP address, and optional machine fingerprint, plus audit and notification records generated as you use the service.',
            },
            {
                heading: 'How we use information',
                body: 'To issue and verify licenses, to enforce activation limits and environment bindings, to send expiry and renewal notifications, to provide support, and to keep an audit trail of administrative actions such as license resets.',
            },
            {
                heading: 'Cookies and similar technologies',
                body: 'Essential cookies keep you signed in and protect the application. See the Cookie Policy for the categories we use and how to control them.',
            },
            {
                heading: 'Sharing and third parties',
                body: 'We do not sell personal data. Data may be processed by infrastructure and payment providers strictly to deliver the service, and disclosed where required by law.',
            },
            {
                heading: 'Retention',
                body: 'License, order, and audit records are retained for as long as needed to honour the license and meet legal obligations. Retention periods not fixed by law are [placeholder].',
            },
            {
                heading: 'Security',
                body: 'License keys are stored hashed with a per-license salt, administrative actions are logged, and offline responses are signed with RSA-SHA256. Signing keys are read through configuration and are never exposed to clients.',
            },
            {
                heading: 'Your rights',
                body: 'You can request access to, correction of, or deletion of your personal data, and object to certain processing. Contact us to exercise these rights.',
            },
            {
                heading: 'International transfers',
                body: 'Where data crosses borders we rely on appropriate safeguards. Details of the safeguards used are [placeholder].',
            },
            {
                heading: 'Changes to this policy',
                body: 'We may update this policy as the service evolves. Material changes will be reflected on this page with an updated revision date.',
            },
            {
                heading: 'Contact',
                body: `Questions about privacy can be sent to ${contact.privacyEmail}.`,
            },
        ],
    },
    terms: {
        title: 'Terms of Service',
        updated: '[placeholder]',
        intro:
            'These terms govern your use of the CoreVisys licensing platform and the licenses issued through it.',
        sections: [
            {
                heading: 'Acceptance',
                body: 'By creating an account or activating a license you agree to these terms. If you do not agree, do not use the service.',
            },
            {
                heading: 'The service',
                body: 'CoreVisys issues, activates, and verifies software licenses. Licenses bind to a domain on first activation and may be bound to an environment fingerprint.',
            },
            {
                heading: 'Licenses and activation limits',
                body: 'Each license carries an activation limit and an expiry date. Trials run for 180 days, after which a full license is required. Exceeding an activation limit or sharing a key may result in the license being suspended.',
            },
            {
                heading: 'Your responsibilities',
                body: 'Keep your license keys and account credentials secure, use the API within its throttling limits, and do not attempt to bypass or forge signature verification.',
            },
            {
                heading: 'Payments and refunds',
                body: `You agree to pay the price shown at checkout. Refund handling is ${contact.refundPolicy === '[placeholder]' ? '[placeholder] and is not stated here' : contact.refundPolicy}. Contact ${contact.supportEmail} for billing questions.`,
            },
            {
                heading: 'Service availability',
                body: `Availability targets and any service-level commitments are ${contact.sla === '[placeholder]' ? '[placeholder]' : contact.sla}. The API fails closed with a 503 error if license signing is temporarily unavailable.`,
            },
            {
                heading: 'Suspension and termination',
                body: 'A license may be suspended or revoked for non-payment, abuse, or breach of these terms. Revoked signing keys are rejected during offline verification.',
            },
            {
                heading: 'Limitation of liability',
                body: 'To the maximum extent permitted by law, CoreVisys is not liable for indirect or consequential losses arising from use of the service.',
            },
            {
                heading: 'Changes to these terms',
                body: 'We may revise these terms. Continued use after a revision means you accept the updated terms.',
            },
            {
                heading: 'Contact',
                body: `Legal questions can be sent to ${contact.legalEmail}.`,
            },
        ],
    },
    cookies: {
        title: 'Cookie Policy',
        updated: '[placeholder]',
        intro:
            'This policy describes the cookies and similar technologies CoreVisys uses and how you can control them.',
        sections: [
            {
                heading: 'What cookies are',
                body: 'Cookies are small files stored by your browser that let a site remember information between requests.',
            },
            {
                heading: 'Cookies we use',
                body: 'Essential cookies keep you signed in and protect requests against cross-site abuse. Preferences such as your selected theme are also remembered so the site looks the same on your next visit.',
            },
            {
                heading: 'Why we use them',
                body: 'To authenticate your session, remember your theme preference, and keep the platform secure. We do not use cookies to sell your data.',
            },
            {
                heading: 'Third-party cookies',
                body: 'Payment providers may set cookies when you complete a checkout. Any third-party cookie use required for a deployment is [placeholder].',
            },
            {
                heading: 'Managing cookies',
                body: 'You can block or delete cookies through your browser settings. Blocking essential cookies will prevent you from signing in.',
            },
            {
                heading: 'Changes to this policy',
                body: 'We may update this policy as the service evolves. Updates will appear on this page.',
            },
            {
                heading: 'Contact',
                body: `Questions about cookies can be sent to ${contact.privacyEmail}.`,
            },
        ],
    },
};