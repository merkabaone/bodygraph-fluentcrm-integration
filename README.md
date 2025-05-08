# BodyGraph FluentCRM Integration

A secure, modular WordPress plugin that integrates BodyGraph API webhooks with FluentCRM for Human Design chart data management and email validation workflows.

---

## Features
- **Webhook integration** with BodyGraph API
- **FluentCRM contact management** (create/update, custom fields)
- **Configurable logging**: Only log summaries by default, or enable full debug logs in settings
- **Email validation workflow**
- **Custom database staging/logging**
- **Asynchronous processing (Action Scheduler)**
- **Security**: Input validation, nonce & capability checks, output escaping, IP rate limiting
- **Modular codebase**: Logger and FluentCRM integration are in dedicated classes

## Requirements
- WordPress 5.0+
- PHP 7.4+
- FluentCRM plugin
- BodyGraph API access

## Installation
1. Copy the `bodygraph-fluentcrm-integration` folder to your WordPress plugins directory
2. Activate the plugin in WordPress admin
3. Configure plugin settings in WordPress admin (List ID, Debug Logs)

## Configuration
- Set up the webhook endpoint in BodyGraph (see plugin settings for endpoint URL)
- Configure FluentCRM List ID (required)
- Enable or disable detailed debug logs as needed

## Security & Best Practices
- **Rate limiting**: Max 10 webhook requests per 10 minutes per IP
- **All input is sanitized and validated**
- **Admin UI**: All output is escaped, all forms use nonces, and only admins can access settings
- **No hardcoded secrets or PII in logs**
- **All logging is filtered by settings**

## Troubleshooting
- Enable debug logs in plugin settings for detailed error and API call logs
- Check `bgfci.log` in the plugin directory for log output
- Common issues: Check List ID configuration, webhook payload structure, and rate limits

## Contributing
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License
[Specify your license here]
