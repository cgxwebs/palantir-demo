<?php


namespace App\AppService;


use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class FacebookDeletionDecoder
{
    private mixed $client_secret;

    public function __construct(ContainerBagInterface $params)
    {
        $this->client_secret = $params->get('facebook.client_secret');
    }

    public function parse_signed_request($signed_request)
    {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        $sig = $this->base64_url_decode($encoded_sig);
        $data = json_decode($this->base64_url_decode($payload), true);


        $expected_sig = hash_hmac('sha256', $payload, $this->client_secret, $raw = true);
        if ($sig !== $expected_sig) {
            throw new \Exception('Invalid data');
        }

        return $data;
    }

    private function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}