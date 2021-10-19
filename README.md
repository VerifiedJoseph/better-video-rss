# BetterVideoRss
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

BetterVideoRss is a PHP script for generating YouTube channel and playlist RSS feeds using YouTube's [Data API](https://developers.google.com/youtube/v3/) and RSS feeds.

### Caching
To reduce requests, responses from YouTube's Data API and RSS feeds are cached. Caching can be disabled via an [environment variable](docs/configuration.md).

## Documentation
* [Configuration](docs/configuration.md)
* [Docker](docs/docker.md)

## Public Instance

A [public instance](https://tools.verifiedjoseph.com/BetterVideoRss/) is available and is hosted by Dreamhost in Virginia, United States. [Example feed](https://tools.verifiedjoseph.com/BetterVideoRss/feed.php?channel_id=UCBa659QWEk1AI4Tg--mrJ2A&format=html)

## Requirements

* PHP >= 8.0
* Composer
* PHP Extensions:
	* [`JSON`](https://www.php.net/manual/en/book.json.php)
	* [`cURL`](https://secure.php.net/manual/en/book.curl.php)
	* [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php)

## Dependencies
(Via [Composer](https://getcomposer.org/))

[`php-curl-class/php-curl-class`](https://github.com/php-curl-class/php-curl-class)

## License

MIT License. Please see [LICENSE](LICENSE) for more information.
