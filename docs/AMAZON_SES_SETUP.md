# Amazon SES Setup For Core

This guide is for configuring real email sending after Phase 9 is deployed. Do not store AWS secrets in Markdown, tickets, prompts, or committed files.

Phase 9B keeps all newsletter sending disabled unless an admin explicitly enables the global kill switch and the sender identity flags. There is still no mass-send pipeline.

## 1. Region

Choose one AWS region for SES sending. Keep SES configuration sets and the SNS topic for SES events in the same region. Store the selected region in Core sender identities and, if using env defaults, in `AWS_DEFAULT_REGION`.

## 2. Verify Sending Domain

In Amazon SES, create a verified identity for the sending domain. Add the DNS records SES provides:

- DKIM CNAME records.
- SPF TXT record if the domain does not already authorize SES.
- DMARC TXT record.
- Custom MAIL FROM records if you choose a MAIL FROM domain.

When DNS is managed by Cloudflare, email authentication records must be DNS-only where applicable, not proxied.

## 3. Sender Identities

A verified domain lets addresses on that domain send mail. Individual email identities can also be verified. In Core, map each sender to `email_sender_identities` with `from_name`, `from_email`, optional `reply_to`, SES region, and configuration set.

## 4. Sandbox And Production Access

New SES accounts may start in sandbox mode. In sandbox, sending is limited and recipients must be verified. Request production access before real campaigns. Explain the owned editorial sites, opt-in flow, unsubscribe handling, suppression processing, and expected warm-up volume.

Warm up gradually. Do not start with high volume.

## 5. Configuration Sets

Create a dedicated SES configuration set for Core. Use it for event publishing and sender identity mapping. Store its name in Core sender identity or newsletter settings.

## 6. SNS Topic

Create an SNS topic in the same region as SES. Add an HTTPS subscription:

```text
https://core.staratlasmedia.com/api/webhooks/aws/sns/ses
```

Confirm the SNS subscription. This endpoint must not be protected by Cloudflare Zero Trust Access or interactive WAF challenges. Core verifies SNS signatures at the application layer.

## 7. Events To Enable

Enable at least:

- Delivery
- Bounce
- Complaint
- Reject
- Rendering Failure
- Delivery Delay

Open and Click are optional. Enable them only with a privacy decision, because opens rely on an image pixel and do not prove reading.

## 8. IAM Credentials

Use least-privilege IAM credentials or a role. Do not use root credentials. Store secrets only in `.env`, a secret manager, or encrypted Core fields.

Suggested env names:

```text
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
AWS_DEFAULT_REGION
CORE_NEWSLETTER_SEND_ENABLED=false
CORE_NEWSLETTER_SNS_TOPIC_ARN=
CORE_NEWSLETTER_SNS_AUTO_CONFIRM=false
SES_CONFIGURATION_SET
SES_DEFAULT_FROM_EMAIL
```

## 9. Cloudflare

Keep `/core-admin*` behind Zero Trust. Do not put the SNS webhook or tracking endpoints behind Access. Use WAF/rate limiting that allows AWS SNS POST requests and image/click tracking.

## 10. Controlled Test Email

The Filament sender identity action sends only to an explicit test recipient and only when all of these are true:

- `CORE_NEWSLETTER_SEND_ENABLED=true`;
- sender identity `send_enabled=true`;
- sender identity `test_send_enabled=true`;
- sender identity `status=active`.

Failed transport attempts are recorded as test results without exposing AWS secrets.

## 11. Deliverability

Use coherent From domains, aligned SPF/DKIM/DMARC, double opt-in, clear unsubscribe links, suppression lists, and bounce/complaint monitoring. Watch complaint rate closely and stop sending if it rises.

## Checklist

- Domain identity verified.
- DKIM validated.
- SPF and DMARC present.
- Production access requested or granted.
- Core configuration set created.
- SNS topic created in same region.
- Core webhook reachable.
- SNS subscription confirmed.
- IAM credentials configured outside Git.
- Core sender identity saved.
- Controlled test email sent only to an explicit test address.
- Bounce and complaint event tests planned.
