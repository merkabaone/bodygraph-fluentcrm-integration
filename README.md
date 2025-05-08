# BodyGraph + FluentCRM integration

A secure, modular WordPress plugin that integrates BodyGraph API webhooks with FluentCRM for Human Design chart data management and email validation workflows.

---

## Features
- **Webhook integration** with BodyGraph API
- **FluentCRM contact management** (create/update, custom fields)
- **Configurable logging**: Only log summaries by default, or enable full debug logs in settings
- **Email validation workflow**
- **Security**: Input validation, nonce & capability checks, output escaping, IP rate limiting

## Requirements
- WordPress 5.0+
- PHP 7.4+
- FluentCRM plugin
- BodyGraph API access

## Installation
1. Copy the `bodygraph-fluentcrm-integration` folder to your WordPress plugins directory
2. Activate the plugin in WordPress admin
3. Configure plugin settings in WordPress admin (List ID, Debug Logs)

## Configuration & usage
- Set up the webhook endpoint in BodyGraph:
    - Go to **WordPress Admin > Settings > BodyGraph FluentCRM**.
    - Copy the **Webhook Endpoint URL** displayed at the top. It will look like:
      
      `https://yourdomain.com/wp-json/bodygraph-fluentcrm/v1/webhook`
      
    - Paste this URL into your BodyGraph API/Webhook configuration.
    - This is where BodyGraph will POST Human Design chart data for each user.
- Specify **FluentCRM List ID** (required):
    - Enter the List ID to which new/updated contacts should be assigned.

## Security & best practices
- **Rate limiting**: Max 10 webhook requests per 10 minutes per IP.
- **Input validation/sanitization**: All webhook and admin input is sanitized and validated.
- **Output escaping**: All admin UI output is escaped.
- **Nonce and capability checks**: All admin actions/forms are protected by nonces and require admin capability.
- **No hardcoded secrets or PII in logs**: Only summary logs by default; detailed logs are opt-in. No secrets or sensitive PII are logged.
- **No secrets in repo**: No hardcoded secrets or sensitive data are committed to the repository.

## Troubleshooting
- Enable debug logs in plugin settings for detailed error and API call logs.
- Check `bgfci.log` in the plugin directory for log output.
- Common issues:
    - **Webhook not triggering:** Check that the endpoint URL is correct and accessible.
    - **Contacts not updating:** Ensure List ID is set and payload has a valid email.
    - **Rate limit errors:** Max 10 requests per 10 minutes per IP.
    - **Fields not updating:** Only custom fields and birthday are updated for existing contacts; all standard fields except birthday are ignored for updates (by design).
    - **Admin settings not saving:** Ensure you have admin rights and nonces are valid.

## Contributing
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Support
For help, bug reports, or questions, email: support@merkaba.one