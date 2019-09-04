# BetterYouTubeRss 
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

BetterYouTubeRss is a PHP script for generating YouTube channel and playlist RSS feeds using the [YouTube Data API](https://developers.google.com/youtube/v3/).

## API Key
A valid [YouTube API key](https://developers.google.com/youtube/registering_an_application) is required.

## Caching
To reduce API requests, responses from the YouTube Data API are cached. Caching can be disabled via the `DISABLE_CACHE` value in the config file.

## Hybrid Mode
Hybrid Mode uses YouTube's RSS feeds and the YouTube Data API to generate feeds. Enabling hybrid mode will reduce the number of requests made to the YouTube API, but will limit the number of videos returned to 15. Hybrid Mode can be enabled via the `ENABLE_HYBRID_MODE` value in the config file.

## Requirements

* PHP >= 7.1
* Composer
* PHP Extensions:
	* JSON [https://secure.php.net/manual/en/refs.xml.php](https://www.php.net/manual/en/book.json.php)
	* cURL [https://secure.php.net/manual/en/book.curl.php](https://secure.php.net/manual/en/book.curl.php)
	* mbstring [https://secure.php.net/manual/en/book.mbstring.php](https://secure.php.net/manual/en/book.mbstring.php)

## Dependencies
(Via [Composer](https://getcomposer.org/))

`php-curl-class/php-curl-class` [https://github.com/php-curl-class/php-curl-class](https://github.com/php-curl-class/php-curl-class)

## License

MIT License. Please see [LICENSE](LICENSE) for more information.
