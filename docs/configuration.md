# Configuration
The preferred method to adjust the configuration is with environment variables.

Alternatively, you can use `config.php` (copied from [`config.php-dist`](../config.php-dist)) to set the variables.

### Reqiured variables
The following two variables must be set for BetterVideoRss to work.

| Name                    | Description                                                                                                 |
| ----------------------- | ----------------------------------------------------------------------------------------------------------- |
| `BVRSS_SELF_URL_PATH`   | Fully qualified URL used to access BetterVideoRss.                                                          |
| `BVRSS_YOUTUBE_API_KEY` | YouTube API Key ([developers.google.com](https://developers.google.com/youtube/registering_an_application)) |

### Optional variables

Optional variables, if set, override the defaults listed in [`Configuration.php`](../include/Configuration.php#L29).

| Name                        | Description                                                               | Default value |
| --------------------------- | ------------------------------------------------------------------------- | ------------- |
| `BVRSS_RAW_API_ERRORS`      | Enables displaying of raw API errors.                                     | `false`       |
| `BVRSS_TIMEZONE`            | Timezone ([php docs](https://www.php.net/manual/en/timezones.php))       | `UTC`         |
| `BVRSS_DATE_FORMAT`         | Date format ([php docs](https://www.php.net/manual/en/function.date.php)) | `F j, Y`      |
| `BVRSS_TIME_FORMAT`         | Time format ([php docs](https://www.php.net/manual/en/function.date.php)) | `H:i`         |
| `BVRSS_CACHE_DIR`           | Cache directory path.                                                     | `cache`       |
| `BVRSS_DISABLE_CACHE`       | Disables caching.                                                         | `false`       |
| `BVRSS_ENABLE_CACHE_VIEWER` | Enables cache viewer.                                                     | `false`       |
| `BVRSS_ENABLE_IMAGE_PROXY`  | Enables video thumbnail image proxy.                                      | `false`       |
