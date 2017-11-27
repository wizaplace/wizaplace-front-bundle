# Changelog

<details>
<summary>Unreleased</summary>

### BREAKING CHANGES

### New features

### Bugfixes

</details>

## 0.2.3

- fix declination ID unserialization

## 0.2.2

### New features

- New `\WizaplaceFrontBundle\Service\FavoriteService` which decorates the SDK's `FavoriteService` with a request-scoped cache
- Translate authentication flash messages
- Set timeouts for API client

## 0.2.1

### Bugfixes

- `\WizaplaceFrontBundle\Service\AuthenticationService::authenticate` was not triggering authentication events
- Update SDK to v1.0.1
- Fix bug causing `DeclinationId` to be ignored by the URL generator when put into the URL query

## 0.2.0

### BREAKING CHANGES

- Upgrade SDK to v1.0.0 (https://github.com/wizaplace/wizaplace-php-sdk/blob/master/CHANGELOG.md#100)
- All `string` declination IDs are now `\Wizaplace\SDK\Catalog\DeclinationId`s instead (due to the SDK upgrade)

## 0.1.0

First version
