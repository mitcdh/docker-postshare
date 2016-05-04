<?php
/**
 * Postshare - https://github.com/mitcdh/docker-postshare
 *
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author     Mitchell Hewes <me@mitcdh.com>
 *
 */

class Postshare
{
    private $s3_target;
    private $aws_bucket_name;
    private $aws_path_prefix;
    private $public_url;
    private $url_shortener_api;
    private $url_shortener_key;

    function __construct($accesskey, $secretkey, $bucketname, $endpoint = null, $pathprefix = null, $publicurl = null, $shortenerurl = null, $shortenerkey = null)
    {
        $aws_endpoint = (!empty($endpoint) ? $endpoint : "s3.amazonaws.com");
        $this->s3_target = new S3($accesskey, $secretkey, true, $aws_endpoint);
        $this->aws_bucket_name = $bucketname;

        if(!empty($pathprefix)) $this->aws_path_prefix = $pathprefix;

        $this->public_url = (!empty($publicurl) ? $publicurl : ("https://" . $this->aws_bucket_name . '.' . $aws_endpoint . "/"));

        if(!empty($shortenerurl)) $this->url_shortener_api = $shortenerurl;
        if(!empty($shortenerkey)) $this->url_shortener_key = $shortenerkey;
    }

    // Adapted from https://github.com/YOURLS/YOURLS/wiki/Remote-API
    private function shorten_url($original_url)
    {
        // Init the CURL session
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $this->url_shortener_api );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );            // No header in the result
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // Return, do not echo result
        curl_setopt( $ch, CURLOPT_POST, 1 );              // This is a POST request
        if(!empty($this->url_shortener_key))
        {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, array(      // Data to POST
                    'url'      => $original_url,
                    'signature'=> $this->url_shortener_key,
                    'format'   => 'json',
                    'action'   => 'shorturl'
                ) );
        }
        else
        {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, array(      // Data to POST
                    'url'      => $original_url,
                    'format'   => 'json',
                    'action'   => 'shorturl'
                ) );
        }
        
        // Fetch and return content
        $data = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($data);
        if(strstr($obj->{'status'},"success"))
        {
            return $obj->{'shorturl'};
        }
        return $original_url;
    }

    // From: https://gist.github.com/liunian/9338301#gistcomment-1570375
    private static function human_filesize($size, $precision = 2) {
        for($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {}
        return round($size, $precision).['B','kB','MB','GB','TB','PB','EB','ZB','YB'][$i];
    }
    
    // Landing page template adapted from https://github.com/DropshareApp/default-landing-page
    private static function landing_page_generator($filename, $extension, $landing_page_location, $filesize, $mimetype)
    {
        $filename = $filename . '.' . $extension;
        $path = $landing_page_location . '/' . $filename;
        $sharedate = date("j M Y \a\\t g:i:s A T");

        switch ($mimetype)
        {
            case 'image/png':
            case 'image/jpeg':
            case 'image/gif':
            case 'image/bmp':
                $preview = "<div class=\"imagePreview\"><img src=" . $path . " alt=\" ". $filename ." \" class=\"img-thumbnail\" /></div>";
                break;

            case "audio/aac":
            case "audio/mp4":
            case "audio/mpeg":
            case "audio/ogg":
            case "audio/wav":
            case "audio/webm":
                $preview = "<div class=\"imagePreview\"><audio controls><source src=" . $path . " type=" . $mimetype . "><span class=\"fa fa-file-audio-o\"></span></audio></div>";
                break;

            case 'video/mp4':
            case 'video/ogg':
            case 'video/webm':
                $preview = "<div class=\"imagePreview\"><video width=\"320\" height=\"240\" controls><source src=" . $path . " type=" . $mimetype . "><span class=\"fa fa-file-video-o\"></span></video></div>";
                break;
                
            default:
                $preview = "<div class=\"imagePreview\"><span class=\"fa fa-file\"></span></div>";
                break;
        }

        return <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="Postshare https://github.com/mitcdh/docker-postshare" />
    <meta property="twitter:card" content="summary" />
    <meta property="twitter:image" content="${path}" [^] />
    <meta property="twitter:title" content="${filename}" />
    <title>${filename}</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
      nav #navbar {
        float:right;
        text-align:right;
      }
      div.container.centered {
        margin: 10px auto;
        text-align:center;
      }
      div.container div.download {
        margin-top: 25px;
      }
      div.container div.download a.btn {
        margin-bottom: 5px;
        font-size: 16pt;
      }
      div.container div.download span.filesize {
        font-size: 10pt;
        color: rgb(160,160,160);
      }

      /* preview types */
      .fa {
          font-size: 100px;
          aria-hidden: true;
      }
      img {
        max-width: 100%;
        max-height: 80vh;
      }
      video, iframe {
        height: auto;
        max-height: 80vh;
        width: 100%;
      }
      iframe {
        height: 80vh;
      }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="${path}">${filename}</a>
        </div>
        <div id="navbar">
          <ul class="nav navbar-nav">
            <li><a href="${path}">Shared on ${sharedate}</a></li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container centered">
      ${preview}

      <div class="download">
        <a href="${path}" class="btn btn-primary">Download</a><br />
        <span class="filesize">${filesize}</span>
      </div>
    </div>
  </body>
</html>
EOT;
    }

    // Modeled after python's similar function: https://docs.python.org/library/base64.html#base64.urlsafe_b64encode
    private static function urlsafe_b64encode($input)
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    public function upload_file($localfile, $filename)
    {
        $pathinfo_array = pathinfo($filename);
        $filename_name = $pathinfo_array['filename'];
        $filename_extension = $pathinfo_array['extension'];

        // mimetype from magic numbers
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $localfile);
        finfo_close($finfo);

        // Generate a name and a landing page
        $filesize = self::human_filesize(filesize($localfile));
        $nonce = self::urlsafe_b64encode(openssl_random_pseudo_bytes(6));
        $landing_page_location = $filename_name . '-' . $nonce;
        $landing_page = self::landing_page_generator($filename_name, $filename_extension, $landing_page_location, $filesize, $mimetype);

        // Update our location with a path prefix
        $landing_page_location = $remotefile = $this->aws_path_prefix . $landing_page_location;

       
        // Upload landing page and offset remotefile if successful
        if(!empty($landing_page) && $this->s3_target->putObjectString($landing_page, $this->aws_bucket_name, $landing_page_location, S3::ACL_PUBLIC_READ, array(), 'text/html'))
        {
            $remotefile = $remotefile . '/' . $filename_name;
        }
        else
        {
            unset($landing_page_location);
        }

        // Add our extension
        $remotefile = $remotefile . '.' . $filename_extension;
        
        // Upload file and shorten if required
        if($this->s3_target->putObjectFile($localfile, $this->aws_bucket_name, $remotefile, S3::ACL_PUBLIC_READ))
        {
            $output = (!empty($landing_page_location) ? ($this->public_url . $landing_page_location) : ($this->public_url . $remotefile));
            return (!empty($this->url_shortener_api) ? $this->shorten_url($output) : $output);
        }
        else
        {
            return null;
        }
    }
}
