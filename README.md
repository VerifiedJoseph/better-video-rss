# BetterVideoRss

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

BetterVideoRss is a PHP script for generating YouTube channel and playlist RSS feeds using YouTube's [Data API](https://developers.google.com/youtube/v3/) and RSS feeds.

## Configuration

Environment variables are used to adjust the configuration. Alternatively, you can use `config.php` (copied from [`config.php-dist`](config.php-dist)).

### Required variables

| Name                    | Type     | Description                                                                                                 |
| ----------------------- | -------- | ----------------------------------------------------------------------------------------------------------- |
| `BVRSS_SELF_URL_PATH`   | `string` | Fully qualified URL used to access BetterVideoRss.                                                          |
| `BVRSS_YOUTUBE_API_KEY` | `string` | YouTube API Key ([developers.google.com](https://developers.google.com/youtube/registering_an_application)) |

### Optional variables

| Name                        | Type      | Default value | Description                                                               |
| --------------------------- | --------- | ------------- | ------------------------------------------------------------------------- |
| `BVRSS_RAW_API_ERRORS`      | `boolean` | `false`       | Enables displaying of raw API errors.                                     |
| `BVRSS_TIMEZONE`            | `string`  | `UTC`         | Timezone ([php docs](https://www.php.net/manual/en/timezones.php))        |
| `BVRSS_DATE_FORMAT`         | `string`  | `F j, Y`      | Date format ([php docs](https://www.php.net/manual/en/function.date.php)) |
| `BVRSS_TIME_FORMAT`         | `string`  | `H:i`         | Time format ([php docs](https://www.php.net/manual/en/function.date.php)) |
| `BVRSS_CACHE_DIR`           | `string`  | `cache`       | Cache directory path.                                                     |
| `BVRSS_DISABLE_CACHE`       | `boolean` | `false`       | Disables caching.                                                         |
| `BVRSS_ENABLE_CACHE_VIEWER` | `boolean` | `false`       | Enables cache viewer.                                                     |
| `BVRSS_ENABLE_IMAGE_PROXY`  | `boolean` | `false`       | Enables video thumbnail image proxy.                                      |

## Documentation

- [Caching](docs/caching.md)
- [Docker](docs/docker.md)

## Requirements

- PHP >= 8.0
- Composer
- PHP Extensions:
  - [`JSON`](https://www.php.net/manual/en/book.json.php)
  - [`cURL`](https://secure.php.net/manual/en/book.curl.php)
  - [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php)

## Dependencies

[`php-curl-class/php-curl-class`](https://github.com/php-curl-class/php-curl-class)

## License

MIT License. Please see [LICENSE](LICENSE) for more information.
