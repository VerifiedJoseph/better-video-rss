# Caching

To reduce requests, responses from YouTube's Data API and RSS feeds are cached.

When caching is enabled, BetterVideoRss will only check for new videos once every 10 minutes.

Caching can be disabled via the `BVRSS_DISABLE_CACHE` [environment variable](configuration.md).

## RSS Feeds

Responses from `https://www.youtube.com/feeds/videos.xml` are cached for 10 minutes.

## API

Caching of responses from the YouTube API varies depending on the endpoint and its function.

| Endpoint    | Cached For | Note                                                          |
| ----------- | ---------- | ------------------------------------------------------------- |
| `/channels` | 30 days    | Endpoint returns channel details.                             |
| `/playlist` | 30 days    | Endpoint returns playlist details.                            |
| `/videos`   | 1 hour     | Endpoint returns video details for a list of given video IDs. |
