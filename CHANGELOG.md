# Changelog

All notable changes to this project are documented in this file.

## [1.6.2](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.6.2) - 2023-12-08

- Dockerfile: Added health check for php-fpm. ([#224](https://github.com/VerifiedJoseph/better-video-rss/pull/224), [`1dd2728`](https://github.com/VerifiedJoseph/better-video-rss/commit/1dd2728146d97ff986047b2f4fd39fa9c1f4bad7))
- Dockerfile: Fixed entrypoint. ([#222](https://github.com/VerifiedJoseph/better-video-rss/pull/222), [`91186f8`](https://github.com/VerifiedJoseph/better-video-rss/commit/91186f8653139593e04ae5edceedc39c27cfe0dc))
- Dockerfile: Updated alpine from 3.18.5 to 3.19.0 ([#226](https://github.com/VerifiedJoseph/better-video-rss/pull/226), [`0b5bb03`](https://github.com/VerifiedJoseph/better-video-rss/commit/0b5bb03580582ef6061a5335aab65f9005b4b970))

## [1.6.1](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.6.1) - 2023-12-08

- FeedFormat: Fixed creating feed format URLs. ([#218](https://github.com/VerifiedJoseph/better-video-rss/pull/218), [`57be490`](https://github.com/VerifiedJoseph/better-video-rss/commit/57be4901c669b1fce53f5aa7d03d09212abd913d))

## [1.6.0](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.6.0) - 2023-12-07

- html: Updated checkbox text and layout on index page. ([#215](https://github.com/VerifiedJoseph/better-video-rss/pull/215), [`b9f67cb`](https://github.com/VerifiedJoseph/better-video-rss/commit/b9f67cb40a65c6357920177e0719029986e3fe4d))
- dockerfile: Replaced supervisor with entrypoint script. ([#213](https://github.com/VerifiedJoseph/better-video-rss/pull/213), [`23ca57e`](https://github.com/VerifiedJoseph/better-video-rss/commit/23ca57e1608773225abef24328617418d4fc5049))
- cache: Added cache format version number. ([#216](https://github.com/VerifiedJoseph/better-video-rss/pull/216), [`e0ff104`](https://github.com/VerifiedJoseph/better-video-rss/commit/e0ff104042467e948ad56e6af52f7ee5274f6bd2))

## [1.5.3](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.5.3) - 2023-12-01

- Updated alpine from 3.18.4 to 3.18.5 ([#204](https://github.com/VerifiedJoseph/better-video-rss/pull/204), [`41a0675`](https://github.com/VerifiedJoseph/better-video-rss/commit/41a067550cb8586b8baca9c7f566fa1a0840f192))

## [1.5.2](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.5.2) - 2023-11-28

- Reworked detecting video premieres. ([#198](https://github.com/VerifiedJoseph/better-video-rss/pull/198), [`e88dff8`](https://github.com/VerifiedJoseph/better-video-rss/commit/e88dff88aafd14004e2e718d5820bfa1652eb2fd))

## [1.5.1](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.5.1) - 2023-11-28

- Cache: Invalidate data when cache and script version numbers do not match. ([#192](https://github.com/VerifiedJoseph/better-video-rss/pull/192), [`d74e295`](https://github.com/VerifiedJoseph/better-video-rss/commit/d74e29580c61fbd78ac6bb2c571c3e442c3b75f0))
- Config: Removed method `getCacheFileExtension()`. ([#194](https://github.com/VerifiedJoseph/better-video-rss/pull/194), [`8c4c4d5`](https://github.com/VerifiedJoseph/better-video-rss/commit/8c4c4d5bb36c36abd9deb14d9c7aab694f8e7e41))

## [1.5.0](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.5.0) - 2023-11-28

- Added option to ignore upcoming video premieres. ([#190](https://github.com/VerifiedJoseph/better-video-rss/pull/190), [`646f876`](https://github.com/VerifiedJoseph/better-video-rss/commit/646f8764782b68f324116279f66f9eb126fcc44d))

## [1.4.0](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.4.0) - 2023-11-24

- Added `Page` namespace. ([#185](https://github.com/VerifiedJoseph/better-video-rss/pull/185), [`1922e38`](https://github.com/VerifiedJoseph/better-video-rss/commit/1922e38bccceea5494dcdd2b911b2246cf78d26d))

## [1.3.1](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.3.1) - 2023-11-03

- Fixed error details handling in `Api` class method `handleError()`. ([#182](https://github.com/VerifiedJoseph/better-video-rss/pull/182), [`c70e58e`](https://github.com/VerifiedJoseph/better-video-rss/commit/c70e58e95d4e5df81637728484cb7b4c195bbac4))

## [1.3.0](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.3.0) - 2023-11-02

- Added encoding setting in `Curl` class. ([#166](https://github.com/VerifiedJoseph/better-video-rss/pull/166), [`465fb58`](https://github.com/VerifiedJoseph/better-video-rss/commit/465fb584c681b7ad7a22ed171304284ec61ec495))
- Refactored `Fetch` class. ([#171](https://github.com/VerifiedJoseph/better-video-rss/pull/171), [`9ea74a8`](https://github.com/VerifiedJoseph/better-video-rss/commit/9ea74a854a661468edaa52b44c32334472cf26f5))
- Fixed HTTP 304 response handling in `Api` class ([#173](https://github.com/VerifiedJoseph/better-video-rss/pull/173), [`7679035`](https://github.com/VerifiedJoseph/better-video-rss/commit/76790359667ff3bec8af2b5025b72f8b83711dce))
- Fixed updating video details in `Data` class method `updateFeed()`. ([#174](https://github.com/VerifiedJoseph/better-video-rss/pull/174), [`1453623`](https://github.com/VerifiedJoseph/better-video-rss/commit/14536231c149e9c0d29ce99663e71e762d4588b4))

## [1.2.0](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.2.0) - 2023-10-31

- Removed dependency [`php-curl-class/php-curl-class`](https://github.com/php-curl-class/php-curl-class).
- Added `Curl` class. ([#153](https://github.com/VerifiedJoseph/better-video-rss/pull/155), [`8a1790c`](https://github.com/VerifiedJoseph/better-video-rss/commit/8a1790cbc50b9b58277fbce6d3c7614106bf5772))
- Added `Json` class. ([#161](https://github.com/VerifiedJoseph/better-video-rss/pull/161), [`0cbea78`](https://github.com/VerifiedJoseph/better-video-rss/commit/0cbea78279f186f830b1d8a76c825ce171f84e3f))
- Renamed feed format classes. ([#162](https://github.com/VerifiedJoseph/better-video-rss/pull/162), [`2e736d7`](https://github.com/VerifiedJoseph/better-video-rss/commit/2e736d71a2d1a55872fe731e57f90e9cb2026886))

## [1.1.0](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.1.0) - 2023-10-24

- Config: Added option to disable content security policy. ([#153](https://github.com/VerifiedJoseph/better-video-rss/pull/153), [`801aac0`](https://github.com/VerifiedJoseph/better-video-rss/commit/801aac03d6db0a425719ea49c07c0c30f48b7122))

## [1.0.0](https://github.com/VerifiedJoseph/better-video-rss/releases/tag/v1.0.0) - 2023-10-23
Initial release
