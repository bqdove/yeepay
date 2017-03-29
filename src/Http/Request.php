<?php
/**
 * User: wangzd
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/3/29
 * Time: 20:14
 */
namespace YeePay\Http;

class Request {


    public $requestUrl;

    public $postField;

    public $postFile;


    public $responseInfo;

    public $responseCode;

    public $curlHandle;

    public function __construct($url,$post,$file="") {
        $this->requestUrl = $url;
        $this->postField = $post;
        $this->postFile = $file;


    }

    public function getHeader()
    {
        $header = array(
            'Content-Type: multipart/form-data',
        );
        return $header;
    }

    public function setPost() {
        // invalid characters for "name" and "filename"
        static $disallow = array("\0", "\"", "\r", "\n");

        // build normal parameters
        foreach ($this->postField as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v),
            ));
        }

        foreach ($this->postFile as $k => $v) {
            switch (true) {
                case false === $v = realpath(filter_var($v)):
                case !is_file($v):
                case !is_readable($v):
                    continue; // or return false, throw new InvalidArgumentException
            }
            $data = file_get_contents($v);
            $v = end(explode(DIRECTORY_SEPARATOR, $v));
//        $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
            $k = str_replace($disallow, "_", $k);
            $v = str_replace($disallow, "_", $v);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
            ));
        }

        // generate safe boundary
        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));

        // add boundary for each parameters
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });

        // add final boundary
        $body[] = "--{$boundary}--";
        $body[] = "";

        // set options
        return @curl_setopt_array($this->curlHandle, array(
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => implode("\r\n", $body),
            CURLOPT_HTTPHEADER => array(
                "Expect: 100-continue",
                "Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
            ),
        ));
    }

    public function send() {
        $this->curlHandle = curl_init();
            curl_setopt($this->curlHandle, CURLOPT_URL, $this->requestUrl);
            curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $this->getHeader());
            curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($this->curlHandle, CURLOPT_BINARYTRANSFER,true);
            $this->setPost();
            curl_setopt($this->curlHandle, CURLOPT_USERAGENT, "Yeepay ZGT PHPSDK v1.1x");
            curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->curlHandle, CURLOPT_HEADER, false);
            curl_setopt($this->curlHandle,CURLOPT_POST, true);

            $response = curl_exec($this->curlHandle);
            $this->responseCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
            $this->responseInfo =curl_getinfo($this->curlHandle);
            curl_close ($this->responseInfo );
            $this->responseInfo = null;
            return $response;

    }
}