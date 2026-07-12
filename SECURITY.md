# Security Policy

## Supported Versions

Currently, only the latest version of Folio PDF is supported with security updates.

| Version | Supported          |
|---------|--------------------|
| Latest  | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability in Folio PDF, please report it responsibly.

### How to Report

**Do not** open a public issue for security vulnerabilities.

Instead, send an email to: security@folio-pdf.dev

Please include:
- A description of the vulnerability
- Steps to reproduce the issue
- Affected versions
- Potential impact
- If available, a suggested fix

### What to Expect

1. **Confirmation**: You will receive a confirmation that your report has been received within 48 hours
2. **Assessment**: We will assess the severity and validity of the report
3. **Resolution**: We will work on a fix and coordinate disclosure with you
4. **Disclosure**: We will release a security update and publish security advisories

### Response Timeline

- **Critical vulnerabilities**: Aim to fix within 7 days
- **High severity**: Aim to fix within 14 days
- **Medium severity**: Aim to fix within 30 days
- **Low severity**: Will be addressed in the next scheduled release

## Security Best Practices

### For Users

- Keep Folio PDF updated to the latest version
- Review changelog for security updates
- Only install from official sources (Composer/Packagist)
- Validate user input before passing to template compiler
- Use template compiler in strict mode for production: `$compiler->setStrict(true);`

### For Developers

- Never commit sensitive data (API keys, passwords)
- Use environment variables for configuration
- Validate and sanitize all user input
- Follow secure coding practices
- Keep dependencies updated

## Security Features

Folio PDF includes several security features:

- **Template sandboxing**: Compiled templates run in isolated scope
- **Strict mode**: Optional strict mode to catch undefined variables
- **Type safety**: Strict types throughout the codebase
- **Immutable data**: Immutable document structure prevents unintended mutations
- **No external dependencies**: Zero runtime dependencies reduces attack surface

## Known Security Considerations

### Template Compilation

- Compiled templates are cached to disk. Ensure cache directory is not publicly accessible
- Set appropriate permissions on cache directory (e.g., 700 or 750)
- Use a dedicated cache directory outside web root

### File Operations

- The library reads template files from the filesystem
- Ensure template files are not user-writable in production
- Validate file paths to prevent directory traversal

### Resource Limits

- Large documents may consume significant memory
- Consider implementing memory limits for PDF generation
- Monitor resource usage in production

## Dependency Management

Folio PDF has zero runtime dependencies, which significantly reduces security risks. However:

- Development dependencies are regularly audited
- Composer audit is run before releases
- Dependencies are updated for security patches promptly

## Security Audits

Periodic security audits are performed on the codebase. Results are:

- [ ] Last audit date: TBD
- [ ] Next scheduled audit: TBD

## Contact

For security-related questions that are not vulnerability reports:
- Open a GitHub Discussion with the "security" tag
- Email: security@folio-pdf.dev

## Disclosure Policy

We follow responsible disclosure:
- Security vulnerabilities are fixed before public disclosure
- Credit is given to reporters (with permission)
- Security advisories are published with releases
- CVEs are requested for critical vulnerabilities

## License

This security policy is part of the Folio PDF project and follows the same MIT License.
