<?php


namespace App\AppService;


use League\OAuth2\Client\Provider\Facebook;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FacebookAuthenticator
{
    private $provider;
    private ?Request $request;

    public function __construct(RequestStack $requestStack, ContainerBagInterface $params)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->provider = new Facebook([
            'clientId' => $params->get('facebook.client_id'),
            'clientSecret' => $params->get('facebook.client_secret'),
            'redirectUri' => $params->get('facebook.redirect_uri'),
            'graphApiVersion' => $params->get('facebook.graph_api_version'),
        ]);
    }

    public function attemptAuthentication(): string
    {
        $code = $this->request->query->get('code');
        $state = $this->request->query->get('state');
        if (empty($code) || empty($state)) {
            return $this->provider->getAuthorizationUrl();
        }
        return '';
    }

    public function getUserInfo()
    {
        $code = $this->request->query->get('code');

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            $user = $this->provider->getResourceOwner($token);
        } catch (\Exception $e) {
            throw $e;
        }

        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
        ];
    }
}