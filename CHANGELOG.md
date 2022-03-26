# Changelog

## [1.9.2] - 2022-03-26
- Fix issues with Supertable / exclude SuperTableBlockElement

## [1.9.1] - 2022-02-08
- Add `softPurge` option in example config
- Drop database table if exists (might happen with a dirty uninstall)

## [1.9.0] - 2021-10-15
- Add `softPurge` option to enable [Soft Purge](https://docs.fastly.com/en/guides/soft-purges) for Fastly.

## [1.8.0] - 2021-02-08
- MySQL 8.0 support (removed ordered FULLTEXT index)
- Switched to `psalm` for static analysis

## [1.7.0] - 2021-01-27
- Craft 3.6 support
- Guzzle dependencies updated
- pgsql `uid_urlhash_idx` ([PR #7436](https://github.com/ostark/upper/pull/42))


## [1.6.0] - 2020-06-16
- Require at least Craft 3.2
- Prevent cache purge on updated drafts or revisions 
- Prevent caching of CP requests (@timkelty)
- Allow to modify cache control from template
- Support for Cloudflare scoped API tokens (@tomdavies)

## [1.5.1] - 2019-05-03
- Prevent purge on resaving

## [1.5.0] - 2019-03-12
- Added support for multiple varnish servers (@larsboldt)
- Fixed a bug when using `keyPrefix` 

## [1.4.2] - 2018-10-11
- Backport schema change to initial migration

## [1.4.1] - 2018-09-06
- Fix initialize `$tags` earlier

## [1.4.0] - 2018-08-24
- Added config `keyPrefix` option to prevent key collisions.
- Always purge section on `Elements::EVENT_AFTER_SAVE_ELEMENT` to make sure status change (disabled>>enabled) clears lists of entries 
- Code cleanup

## [1.3.7] - 2018-07-10
### Added
- Varnish: `headers` config option 
- Varnish: purgeAll() 

## [1.3.6] - 2018-06-18
### Changed
- DB fallback: Schema - changed `url` field from `varchar(255) to `text` to allow longer urls
- DB fallback: Schema - Added `urlHash`


## [1.3.5] - 2018-05-26
### Changed
- DB fallback: Schema - changed `tags` field from `varchar(255) to `text` to allow more tags

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


