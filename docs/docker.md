# Docker

BetterVideoRss can be quickly deployed using the included [`Dockerfile`](../Dockerfile).

## Setup

1. Clone the repository (`git clone https://github.com/VerifiedJoseph/BetterVideoRss`).

2. Configure the [environment variables](configuration.md).

3. Build the image: `docker build -t BetterVideoRss {cloned repo path}`

4. Create the container: `docker create --name=BetterVideoRss --publish 8080:80 BetterVideoRss`. Include `--env-file=.env` in the command if using a `.env` file.

5. If the container is created successfully, BetterVideoRss will be running at `http://127.0.0.1:8080`.
