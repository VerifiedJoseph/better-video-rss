<?php

// Self URL Path
// The fully qualified URL used to access BetterVideoRss.
putenv('BVRSS_SELF_URL_PATH=https://example.com/BetterVideoRss/');

// YouTube API Key
// https://developers.google.com/youtube/v3/getting-started
putenv('BVRSS_YOUTUBE_API_KEY=');

// Raw API Errors
// Display raw API errors
putenv('BVRSS_RAW_API_ERRORS=false');

// Timezone
// https://www.php.net/manual/en/timezones.php
putenv('BVRSS_TIMEZONE=UTC');

// Date Format
// https://www.php.net/manual/en/function.date.php
putenv('BVRSS_DATE_FORMAT=F j, Y');

// Time Format
// https://www.php.net/manual/en/function.date.php
putenv('BVRSS_TIME_FORMAT=H:i');

// Cache directory
// Name or path of the directory that cache files are saved to.
putenv('BVRSS_CACHE_DIR=cache');

// Disable Cache
putenv('BVRSS_DISABLE_CACHE=false');

// Enable cache viewer
putenv('BVRSS_ENABLE_CACHE_VIEWER=false');

// Enable image proxy
putenv('BVRSS_ENABLE_IMAGE_PROXY=false');

// Disable content security policy
// https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
putenv('BVRSS_DISABLE_CSP=false');
