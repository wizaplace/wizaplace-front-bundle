# Changelog

<details>
<summary>Unreleased</summary>

### BREAKING CHANGES

### New features

### Bugfixes

</details>

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
