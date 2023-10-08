# Caching

Data returned by YouTube's Data API and RSS feeds is cached to reduce requests and speed up load times. When caching is enabled, BetterVideoRss only allows checking for new videos every 10 minutes.

Caching can be disabled with the `BVRSS_DISABLE_CACHE` [environment variable](../README.md#configuration).

## RSS Feeds

Responses from `https://www.youtube.com/feeds/videos.xml` are cached for 10 minutes.

## API

Caching of responses from the YouTube API varies depending on the endpoint and its function.

| Endpoint    | Cached  | Note                                                          |
| ----------- | ------- | ------------------------------------------------------------- |
| `/channels` | 30 days | Endpoint returns channel details.                             |
| `/playlist` | 30 days | Endpoint returns playlist details.                            |
| `/videos`   | 1 hour  | Endpoint returns video details for a list of given video IDs. |
