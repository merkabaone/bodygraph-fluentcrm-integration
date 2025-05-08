# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.2.x   | :white_check_mark: |
| 1.1.x   | :white_check_mark: |
| < 1.1   | :x:                |

## Security Practices
- **Input validation/sanitization**: All webhook and admin input is sanitized and validated.
- **Output escaping**: All admin UI output is escaped.
- **Nonce and capability checks**: All admin actions/forms are protected by nonces and require admin capability.
- **Rate limiting**: Webhook endpoint is protected by IP-based rate limiting (10 requests per 10 minutes per IP).
- **Logging policy**: Only summary logs by default; detailed logs are opt-in. No secrets or sensitive PII are logged.
- **No secrets in repo**: No hardcoded secrets or sensitive data are committed to the repository.

## Reporting a Vulnerability

We take all security bugs in this plugin seriously. Please email us at support@merkaba.one with a detailed description of the issue, steps to reproduce, and any relevant information. **Do not create a public GitHub issue for security vulnerabilities.**

You can expect a response within 48-72 hours. We will coordinate with you on remediation and public disclosure as appropriate.

Thank you for helping keep BodyGraph FluentCRM Integration and its users safe.
