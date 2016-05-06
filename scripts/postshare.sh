#!/bin/sh

FILE=config.php
WEB_PATH=/www/

if [ -e $WEB_PATH$FILE ]; then
  echo "restart check: installed"
else
  echo "starting installation"
    if [ -z "$AWS_ACCESS_KEY" ]; then
            echo "no AWS_ACCESS_KEY detected -> EXIT"
            exit 1
    else
            echo "AWS_ACCESS_KEY name: $AWS_ACCESS_KEY"
    fi

    if [ -z "$AWS_SECRET_KEY" ]; then
            echo "no AWS_SECRET_KEY detected -> EXIT"
            exit 1
    else
            echo "found AWS_SECRET_KEY"
    fi
    
    if [ -z "$AWS_ENDPOINT" ]; then
            echo "no AWS_ENDPOINT found -> default is s3.amazonaws.com"
            AWS_ENDPOINT="s3.amazonaws.com"
    else
            echo "AWS_ENDPOINT: $AWS_ENDPOINT"
    fi
    
    if [ -z "$AWS_BUCKET_NAME" ]; then
            echo "no AWS_BUCKET_NAME found -> EXIT"
            exit 1
    else
            echo "AWS_BUCKET_NAME: $AWS_BUCKET_NAME"
    fi
    
    if [ -z "$AWS_PATH_PREFIX" ]; then
            echo "no AWS_PATH_PREFIX found"
            AWS_PATH_PREFIX = "";
    else
            echo "AWS_PATH_PREFIX: $AWS_PATH_PREFIX"
    fi

    if [ -z "$PUBLIC_URL" ]; then
            PUBLIC_URL = 'http://' . $AWS_BUCKET_NAME . '.' . $AWS_ENDPOINT;
            echo "no PUBLIC_URL found -> defaulting to $PUBLIC_URL"
    else
            echo "PUBLIC_URL: $PUBLIC_URL"
    fi

    if [ -z "$URL_SHORT_API" ]; then
            echo "no URL_SHORT_API found"
            URL_SHORT_API = "";
    else
            echo "found URL_SHORT_API"
    fi

    if [ -z "$URL_SHORT_KEY" ]; then
            echo "no URL_SHORT_KEY found"
            URL_SHORT_KEY = "";
    else
            echo "found URL_SHORT_KEY"
    fi

    /bin/cat >$WEB_PATH$FILE <<EOL
<?php
define( 'AWS_ACCESS_KEY', '$AWS_ACCESS_KEY' );
define( 'AWS_SECRET_KEY', '$AWS_SECRET_KEY' );
define( 'AWS_ENDPOINT', '$AWS_ENDPOINT' );
define( 'AWS_BUCKET_NAME', '$AWS_BUCKET_NAME' );
define( 'AWS_PATH_PREFIX', '$AWS_PATH_PREFIX' );
define( 'PUBLIC_URL', '$PUBLIC_URL' );
define( 'URL_SHORT_API', '$URL_SHORT_API' );
define( 'URL_SHORT_KEY', '$URL_SHORT_KEY' );

EOL
 
fi
