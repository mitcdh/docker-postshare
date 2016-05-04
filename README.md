# Postshare

I wanted a way to generate [Dropshare](https://getdropsha.re) equivalent uploads from Tweetbot/other platforms so quickly threw this together.

### Environment Variables

* `AWS_ACCESS_KEY`: AWS Access Key
* `AWS_SECRET_KEY`: AWS Secret Key
* `AWS_BUCKET_NAME`: Bucket name to upload to
* `AWS_ENDPOINT`: AWS Endpoint DNS name
* `AWS_PATH_PREFIX`: Path prefix to upload to
* `PUBLIC_URL`: Public URL/CNAME to access the files
* `URL_SHORT_API`: URL of URL Shortening API, only tested with YOURLS
* `URL_SHORT_KEY`: Optional URL shortening API key

### Usage
#### Running the Docker Container
````bash
docker run -d \
    --name postshare \
    -e AWS_ACCESS_KEY="some-key" \
    -e AWS_SECRET_KEY="some-other-key" \
    -e AWS_BUCKET_NAME="bucket-name" \
    -e AWS_ENDPOINT="s3.amazonaws.com" \
    -e AWS_PATH_PREFIX="s/" \
    -e PUBLIC_URL="https://my.public.url/" \
    -e URL_SHORT_API="https://yours.something/yourls-api.php" \
    -e URL_SHORT_KEY="some-other-other-key" \
    mitcdh/postshare
````

#### Post-install Curl
```bash
curl -X POST -F "media=@/file/location" https://api.location.com
```

Or add to Tweetbot or any application which respects similar [Custom Media Uploads](http://tapbots.net/tweetbot/custom_media/)
