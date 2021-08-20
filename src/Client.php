<?php

namespace GQL;

class Client
{
    private $_endpoint;
    public $headers = ["Accept" => "application/json"];
    public $auth = [];
    private $guzzle_client_config = [];
    public $token;

    const CONFIG_FIELDS = ['__args', '__alias', '__aliasFor', '__variables', '__directives', '__on', '__typeName'];

    public function __construct(string $endpoint, array $guzzle_client_config = [])
    {
        $this->_endpoint = $endpoint;
        $this->guzzle_client_config = array_merge($this->guzzle_client_config, $guzzle_client_config);
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function query(array $query)
    {
        return $this->request(Builder::Query($query));
    }


    public function subscription(string $name, array $subscription, array $multipart = [])
    {
        $builder = new Builder("subscription");
        $builder->selectors = Builder::_([$name => $subscription]);
        return $this->request($builder, $multipart);
    }

    public function mutation(string $name, array $mutation, array $multipart = [])
    {
        $builder = new Builder("mutation");
        $builder->selectors = Builder::_([$name => $mutation]);
        return $this->request($builder, $multipart);
    }

    public function request(string $query, array $multipart = []): array
    {
        $http = new \GuzzleHttp\Client($this->guzzle_client_config);

        $uri = $this->_endpoint;
        if ($this->token) {
            $uri .= "?token=$this->token";
        }
        try {
            if ($multipart) {
                $m = $multipart;
                $m[] = [
                    "name" => "query",
                    "contents" => $query
                ];
                $resp = $http->request("POST", $uri, [
                    "auth" => $this->auth,
                    "headers" => $this->headers,
                    "multipart" => $m
                ]);
            } else {
                $resp = $http->request("POST", $uri, [
                    "auth" => $this->auth,
                    "headers" => $this->headers,
                    "json" => [
                        "query" => $query
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return ["error" => ["message" => $e->getMessage()]];
        }
        return json_decode($resp->getBody()->getContents(), true);
    }
}
