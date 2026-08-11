# Esubiz SSO Client

Reusable SSO client for Esubiz applications and subdomains.

Applications install this package and configure:

- `ESUBIZ_SSO_ISSUER`
- `ESUBIZ_SSO_CLIENT_ID`
- `ESUBIZ_SSO_CLIENT_SECRET`
- `ESUBIZ_SSO_REDIRECT_URI`
- `ESUBIZ_SSO_SCOPES`

The package communicates with the central Esubiz SSO authorization server.

Applications do not implement their own OAuth protocol.
