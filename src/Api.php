<?php

namespace NFleet;
include('../httpful.phar');

class Api
{
    public $url = '';
    public $username = '';
    public $password = '';
    public $currentToken = null;
    private $ifNoneMatch = 'If-None-Match';

    public function __construct($url, $uname, $passw) {
        $this->url = $url;
        $this->username = $uname;
        $this->password = $passw;
    }

    public function authenticate() {
        $this->authenticateAndGetTokenData($this->username, $this->password);
        return $this->currentToken;
    }

    public function getRoot() {
        $response = \Httpful\Request::get($this->url.'/')
            ->addHeader("Authorization", $this->getAuthorizationToken())
            ->addHeader("Accept","application/json")
            ->follow_redirects(false)
            ->send();
        return $response->body;
    }

    public function navigate($link, $data = null, $queryParams = null) {
        $request = \Httpful\Request::init()
            ->addHeader("Authorization", $this->getAuthorizationToken())
            ->method($link->Method)
            ->uri($this->url.$link->Uri)
            ->addHeader("Accept",$link->Type)
            ->contentType($link->Type);

        if (($link->Method === "POST" || $link->Method === "PUT") && $data !== null) {
            if (property_exists($link, 'VersionNumber')) {
                $request->addHeader($this->ifNoneMatch, $link->VersionNumber);
            }
            $request->body(json_encode($data));
        }
        $response = $request->send();

        $result = null;
        if ($link->Method === "GET") {
            $result = $response->body;

            if(isset($response->headers['etag'])){
                $result->VersionNumber = $response->headers['etag'];
            }

        } elseif ($link->Method === "POST" || $link->Method === "PUT") {
            $result = $this->createResponseData($response->headers['location'], $response->headers['Content-Type']);
        }

        return $result;
    }

    private function getAuthorizationToken() {
        return $this->currentToken->TokenType.' '.$this->currentToken->AccessToken;
    }

    private function createResponseData($location, $contentType) {
        $path = parse_url($location, PHP_URL_PATH);
        $link = new \stdClass();
        $link->Method = "GET";
        $link->Rel = "location";
        $link->Uri = $path;
        $link->Type = $contentType;
        return $link;
    }

    private function authenticateAndGetTokenData($key, $secret) {
        $authenticationUrl = $this->getAuthLocation();
        if ( $authenticationUrl === null) {
            return;
        }
        else {
            $tokenLocation = $this->authenticateAndGetTokenLocation( $key, $secret, $authenticationUrl );
            $this->currentToken = $this->requestToken($tokenLocation);
        }
    }

    private function requestToken($location) {
        $response = \Httpful\Request::get($location)
            ->addHeader("Accept","application/json")
            ->expects("application/json")
            ->follow_redirects(false)
            ->send();
        return $response->body;
    }

    private function getAuthLocation() {
        $uri = $this->url;
        $response = \Httpful\Request::get($uri)->send();

        return $response->headers['location'];
    }

    private function authenticateAndGetTokenLocation( $key, $secret, $authenticationUrl ){
        $base64encoded = base64_encode($key.':'.$secret);
        $response = \Httpful\Request::post($authenticationUrl)
            ->addHeader('Authorization', 'Basic '.$base64encoded)
            ->body("{ 'Scope': 'data optimization' }")
            ->follow_redirects(false)->send();

        return $response->headers['location'];
    }
}