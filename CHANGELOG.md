# Changelog

<details>
<summary>Unreleased</summary>

### New features

### BREAKING CHANGES

### Bugfixes

</details>

## Next version

### 0.8.0
- Update the PHP SDK to 1.146

### 0.6.2
- Update Doctrine cache.

### 0.6.1
- Update the Wizaplace PHP-SDK to accept at least the 1.108.28 version.

### 0.6.0
- Update the Wizaplace PHP-SDK
- Update the Sitemap generator component to follow the SDK update.

### 0.5.9

- Use PHPCS [wizaplace/phpcs](https://github.com/wizaplace/phpcs)
- Update PHPStan

### 0.5.8

- Debug logs for translations commands (ex.: `bin/console wizaplace:translations:push -vvv`)

## 0.5.7

### Bugfixes

- Default English translation for number separators
- Use French separators if their translation is not found

## 0.5.6

### Bugfixes

- Add services for BOF pecheur

## 0.5.5

### Bugfixes

- Fix sitemap generation for multilingual projects

## 0.5.4

### Bugfixes

- Set OrganisationService as public

## 0.5.3

### Bugfixes

- Forcing logout SSO

## 0.5.2

### New features

- Use request-scoped cache for favorites count

## 0.5.1

### Bugfixes

- Do not include non-GET route in the sitemap

## 0.5.0

### New features

- Handles multilang sitemap

### BREAKING CHANGES

- This bundle now depends on `kphoen/sitemap-generator` for sitemap generation instead of `kphoen/sitemap-bundle`

## 0.4.5

### Bugfixes

 - Fix oauth connection bug if the user already logged in

## 0.4.4

### New features

 - Added `WizaplaceFrontBundle\Controller\OauthController`
 - Added `WizaplaceFrontBundle\Security\LoginManager`

## 0.4.3

 - Improve translations pull (automated cache clear)

## 0.4.2

### New features

 - Upgrade SDK to v1.24.0
 - Add application token support via `%wizaplace.application_token%` Symfony parameter

## 0.4.1

### New features

 - `\WizaplaceFrontBundle\Service\BasketService::addCoupon` now throws detailed exceptions
 - `\WizaplaceFrontBundle\Service\BasketService::checkout` now throws detailed exceptions
 - Upgrade SDK to v1.19.0

## 0.4.0

### New features

 - Upgrade to Symfony 3.4

## 0.3.5

### New features

 - Upgrade SDK to v1.18.0
 - Add an optional GeoFilter parameter to `\WizaplaceFrontBundle\Service\JsonSearchService::search`

## 0.3.4

### New features

 - Update `\WizaplaceFrontBundle\Twig\AppExtension::formatPrice`. It now returns spans and css classes in order to customize the price display.

## 0.3.3

 - Use `price.currency` translation key instead of just `currency`

## 0.3.2

### New features

 - Add cache on `getCategoryTree` which avoids loading the tree several times during a single request
 - Set `%locales%` default value to `%kernel.default_locale%`, facilitating the transition from version 0.2.* to >=0.3.0

## 0.3.1

### New features

 - Make `\WizaplaceFrontBundle\Twig\AppExtension::formatPrice` use the `currency` translation key instead of `€`.
 - Upgrade SDK to v1.15.1
 - Added `\WizaplaceFrontBundle\Service\JsonSearchService`

### Bugfixes

 - Fix a bug causing us to try to merge a basket with itself

## 0.3.0

### BREAKING CHANGES

 - Instead of the `locale` parameter, we now expects a `locales` array parameter containing all available locales

## 0.2.20

### New features

 - `\WizaplaceFrontBundle\Security\User` now forwards getter calls to `\Wizaplace\SDK\User\User` (no need to use `getWizaplaceUser` anymore)
 - the request's locale is now automatically set into the ApiClient, opening the way for multi-lang

## 0.2.19

### New features

 - Added `\WizaplaceFrontBundle\Service\BasketService::setPickupPoint`

## 0.2.18

### New features

 - Added "userFavoriteIds" variable in CategoryController::viewAction
 - Upgraded SDK to v1.14.0
 - Added `\WizaplaceFrontBundle\Service\AuthenticationService::getInitiateResetPasswordForm`
 - Added `\WizaplaceFrontBundle\Service\AuthenticationService::initiateResetPassword`
 - Deprecated `\WizaplaceFrontBundle\Controller\AuthController::initiateResetPasswordAction`

## 0.2.17

### New features

 - Added `\WizaplaceFrontBundle\Service\ProductListService::getLatestProductsWithAttributeChecked`
 - Added `\WizaplaceFrontBundle\Service\FavoriteService::getFavoriteIds`
 - `\WizaplaceFrontBundle\Service\FavoriteService::getAll` does not throw `AuthenticationRequired` exceptions anymore, instead it returns an empty array
 - `\WizaplaceFrontBundle\Service\FavoriteService::isInFavorites` does not throw `AuthenticationRequired` exceptions anymore, instead it returns `false`
 - Added `\WizaplaceFrontBundle\Service\DeclinationService::listProductOptionSelectsFromSelectedDeclination`
 - Added `\WizaplaceFrontBundle\Service\DeclinationService::listProductOptionSelectsFromSelectedVariantsIds`
 - Marked `\WizaplaceFrontBundle\Controller\ProductController::viewAction` as deprecated
 - Upgraded SDK to v1.12.0

## 0.2.16

### New features

 - Reduce the number of basket-related API calls
 - Upgraded SDK to v1.11.0

## 0.2.15

### New features

 - Added `\WizaplaceFrontBundle\Security\User::ROLE_VENDOR` as a user role for vendors
 - Upgraded SDK to v1.10.0

## 0.2.14

### New features

 - Added log for Guzzle cache events
 - Added `\WizaplaceFrontBundle\Twig\AppExtension::getFavoritesCount`

## 0.2.13

### Bugfixes

 - Don't try to get the user's basket ID on anonymous authentication

## 0.2.12

### New features

 - Added Guzzle middleware for HTTP caching
 - Upgraded SDK to v1.8.0
 - Added `image` field to options' variants in `\WizaplaceFrontBundle\Controller\ProductController::viewAction`

## 0.2.11

### New features

 - Added optional `\WizaplaceFrontBundle\Service\SitemapGenerator`, meant to be used with `kphoen/sitemap-bundle`

## 0.2.10

### Bugfixes

 - fix "Illegal offset [..]" error in `\WizaplaceFrontBundle\Controller\ProfileController::createOrderReturnAction`

## 0.2.9

### New features

 - Added `\WizaplaceFrontBundle\Service\ContactService::contact`
 - Upgraded SDK to v1.6.0

## 0.2.8

### New features

 - update SDK to v1.5.1

### Bugfixes

 - prevent upgrade to Symfony 3.4


## 0.2.7

### Bugfixes

 - remove obsolete `getCurrentUser` from Twig Extension, as the corresponding method was already deleted

## 0.2.6

### New features

 - Deprecated `\WizaplaceFrontBundle\Controller\CmsController`
 - Upgraded SDK to v1.3.1

## 0.2.5

### New features

 - Add `\WizaplaceFrontBundle\Service\InvoiceService::downloadPdf`
 - Add a route to allow product preview from back office
 - SDK updated from `1.0.1` to `1.2.0`

### Bugfixes

 - Authentication token was not properly stored when calling `\WizaplaceFrontBundle\Service\AuthenticationService::authenticate`: now fixed

## 0.2.4

### New features

 - make API client timeouts configurable

## 0.2.3

### Bugfixes

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
