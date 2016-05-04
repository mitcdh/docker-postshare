<?php

require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/config.php";


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(isset($_FILES['media']) && (isset($_FILES['media']['tmp_name']) && isset($_FILES['media']['name'])))
    {
        $localfile     = $_FILES['media']['tmp_name'];
        $filename      = $_FILES['media']['name'];
        if(!empty($localfile) && !empty($filename))
        {   
            $ps = new Postshare(AWS_ACCESS_KEY, AWS_SECRET_KEY, AWS_BUCKET_NAME, AWS_ENDPOINT, AWS_PATH_PREFIX, PUBLIC_URL, URL_SHORT_API, URL_SHORT_KEY);
            $url = $ps->upload_file($localfile, $filename);
            
            if(!empty($url))
            {
                    http_response_code(201); // Created
                    $response       = array
                    (
                        "url" => $url
                    );
                    echo json_encode($response);
            }
            else
            {
                    http_response_code(500); // Internal Server Error
            }
        }
        else
        {
            http_response_code(400); // Bad Request
        }
    }
    else
    {
        http_response_code(400); // Bad Request
    }
}
else
{
    http_response_code(405); // Method not allowed
}
