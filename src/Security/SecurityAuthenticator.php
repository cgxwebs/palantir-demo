<?php

namespace App\Security;

use App\Repository\ChatroomRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, private EntityManagerInterface $entityManager, private ChatroomRepository $chatroomRepository, private ParticipantRepository $participantRepository)
    {
    }

    public function authenticate(Request $request): PassportInterface
    {
        $key_hash = $request->request->get('key_hash', '');
        $chatroom_name = $request->attributes->get('name');

        $request->getSession()->set('chatroom_name', $chatroom_name);

        foreach (['key_hash', 'participant_key', 'recipient_key'] as $item) {
            $request->getSession()->set($item, $request->request->get($item, ''));
        }

        return new SelfValidatingPassport(
            new UserBadge($key_hash),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse('/');
    }

    protected function getLoginUrl(Request $request): string
    {
        $from_request = $request->attributes->get('name');
        $from_session = $request->getSession()->get('chatroom_name');
        $att_name = 'not_found';
        if (!empty($from_request)) {
            $att_name = $from_request;
        } elseif (!empty($from_session)) {
            $att_name = $from_session;
        }
        return $this->urlGenerator->generate(self::LOGIN_ROUTE, ['name' => $att_name]);
    }
}
