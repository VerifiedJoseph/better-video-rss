# BetterYouTubeRss 
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

BetterYouTubeRss is a PHP script for generating YouTube channel RSS feeds using the [https://developers.google.com/youtube/v3/](YouTube Data API).

## API Key
A valid [YouTube API key](https://developers.google.com/youtube/registering_an_application) is required.

## Caching
To reduce API requests responses from the YouTube Data API are cached.
Channel responses are cached for 10 days, playlists for 15 mintues and videos for 6 hours.
Caching can be disabled via the `DISABLE_CACHE` value in the config file.

## Requirements

* PHP >= 7.1
* Composer
* PHP Extensions:
	* JSON [https://secure.php.net/manual/en/refs.xml.php](https://www.php.net/manual/en/book.json.php)
	* cURL [https://secure.php.net/manual/en/book.curl.php](https://secure.php.net/manual/en/book.curl.php)

## Dependencies
(Via [Composer](https://getcomposer.org/))

`php-curl-class/php-curl-class` [https://github.com/php-curl-class/php-curl-class](https://github.com/php-curl-class/php-curl-class)

## License

MIT License. Please see [LICENSE](LICENSE) for more information.
