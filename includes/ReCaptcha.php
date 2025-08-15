<?php
namespace Includes;

class ReCaptcha {
    private $secret;
    private $verify_url = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct($secret) {
        $this->secret = $secret;
    }

    public function verify($response, $remoteip = null) {
        $data = [
            'secret' => $this->secret,
            'response' => $response
        ];

        if ($remoteip) {
            $data['remoteip'] = $remoteip;
        }

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $verify = file_get_contents($this->verify_url, false, $context);
        
        return json_decode($verify);
    }
}