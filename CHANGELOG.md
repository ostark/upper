# Changelog

## [1.3.4] - 2018-05-08
### Changed
- API request exception: Show at least http status if no message is available 

## [1.3.3] - 2018-05-02
### Changed
- Adjusted the length of the uid on mysql (cache tag fallback) 

## [1.3.2] - 2018-04-05
### Changed
- Changed version constraint to `craftcms/cms: ^3.0.0`

## [1.3.1] - 2018-03-16
### Fixed
- Category Elements are now supported

## [1.3.0] - 2018-02-23
### Added
- Added **Cloudflare** support

## Changed
- Custom exceptions for KeyCDN, Fastly, Cloudflare API calls
- HTML body is not stored in DB anymore when `useLocalTags` is enabled
- Renamed `EventRegistrar::registerDashboardEvents()` to `EventRegistrar::registerCpEvents()`

## [1.2.0] - 2018-01-23
### Added
- Added DB fallback for pgsql
 
### Fixed
- PHP 7.2 issue (Object is reserved word)

## [1.1.3] - 2018-01-23
### Fixed
- Fixed install bug when using PostgreSQL

## [1.1.2] - 2018-01-22
### Fixed
- Prevent DB fallback when using PostgreSQL

## [1.1.1] - 2017-12-05
### Changed
- Requires Craft 3.0.0-RC1

## [1.1.0] - 2017-11-24
### Added
- Added DB fallback (mysql) 
- Added Config `useLocalTags option 
- Add `X-Upper-Cache` header with cache date or `BYPASS`

### Changed
- Requires Craft 3.0.0-RC1 (alias)

## [1.0.0] - 2017-10-23
### Added
- Auto-tagging for elements, sections and structures
- Auto-invalidation on entry updates and changes in sections and structures
- Keycdn driver
- Fastly driver
- Varnish driver
- Dummy driver


