<?php

namespace  Vendor\SymfonyKeycloakBundle\Infrastructure\Symfony\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Vendor\SymfonyKeycloakBundle\Application\UseCase\AuthenticateUser;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class KeycloakAuthenticator extends AbstractAuthenticator
{

    public function __construct(
        private readonly AuthenticateUser $authenticateUser
    ) {}


    public function authenticate(Request $request): Passport
    {

        $token = $request->headers->get('Authorization');
        if (empty($token)) {
            throw new AuthenticationException("No Token provided");
        }

        try {
            $domainUser = $this->authenticateUser->execute($token);
        } catch (AuthenticationException $exception) {
            throw new \Exception("Error: ", $exception->getMessage());
        }

        $symfonyUser = new SymfonyUser($domainUser);
        return new SelfValidatingPassport(
            new UserBadge(
                $symfonyUser->getUserIdentifier(),
                fn() => $symfonyUser // 🔥 restrict Symfony to search a UserProvider
            )
        );
    }

    public function supports(Request $request): ?bool
    {
        throw new \Exception('Not implemented');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }
}
