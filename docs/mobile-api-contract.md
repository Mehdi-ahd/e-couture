# e-couture Mobile API Contract

## Auth

Primary contract:

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/social`
- `GET /api/auth/me`
- `POST /api/auth/logout`

Expected normalized auth response keys:

- `token`
- `token_type`
- `user`
- `needs_onboarding`

Compatibility path kept for old clients:

- `POST /api/mobile/auth/sync`

## Mobile Domain Endpoints

- `/api/mobile/workspace`
- `/api/mobile/patterns`
- `/api/mobile/clients`
- `/api/mobile/clients/{client}/measurement-sheets`
- `/api/mobile/clients/{client}/orders`
- `/api/mobile/patterns/{pattern}/pieces`
- `/api/mobile/pieces/{piece}/dispositions`
- `/api/mobile/scan`

## Contract Notes

- Google authentication may use Flutter-side identity providers, but the backend must still issue the final Sanctum token.
- Mobile serializers should stay explicit and defensive around missing nested relations.
- Changes to response payloads must be synchronized immediately with Flutter DTOs and consumers.
