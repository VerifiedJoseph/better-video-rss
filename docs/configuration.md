# Configuration
The preferred method to adjust the configuration is via environment variables.

Alternatively, you can use `config.php` (copied from [`config.php-dist`](../config.php-dist)) to override the defaults.

### Reqiured variables
The following two variables must be set or BetterVideoRss will not work.

| Name      			| Description										 |
|--						| --												 |
|`BVRSS_SELF_URL_PATH`	| Fully qualified URL used to access BetterVideoRss. |
|`BVRSS_YOUTUBE_API_KEY`| YouTube API Key 									 |

### Optional variables

Optional variables, if set, override the defaults listed in [`Configuration.php`](../include/Configuration.php#L24).

| Name      					| Description							| Default value |
|--								| --									|--				|
|`BVRSS_RAW_API_ERRORS`			| Enables displaying of raw API errors.	| `false`		|
|`BVRSS_TIMEZONE`				| Time-zone ([php docs](https://www.php.net/manual/en/timezones.php))							|`UTC`			|
|`BVRSS_DATE_FORMAT`			| Date format ([php docs](https://www.php.net/manual/en/function.date.php))						|`F j, Y`		|
|`BVRSS_TIME_FORMAT`			| Time format ([php docs](https://www.php.net/manual/en/function.date.php))						|`H:i`			|
|`BVRSS_CACHE_DIR`				| Cache directory path. 				|`cache`		|
|`BVRSS_DISABLE_CACHE`			| Disables caching.						|`false`		|
|`BVRSS_ENABLE_CACHE_VIEWER`	| Enables cache viewer.			  		|`false`		|
|`BVRSS_ENABLE_IMAGE_PROXY`		| Enables video thumbnail image proxy.	|`false`		|