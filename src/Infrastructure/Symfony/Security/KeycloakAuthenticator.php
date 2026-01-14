<?php

namespace  Vendor\SymfonyKeycloakBundle\Infrastructure\Symfony\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Vendor\SymfonyKeycloakBundle\Application\UseCase\AuthenticateUser;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Vendor\SymfonyKeycloakBundle\Infrastructure\Keycloak\LoginUrlGenerator;
use Vendor\SymfonyKeycloakBundle\Infrastructure\Symfony\Models\SymfonyUser;

class KeycloakAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{

    public function __construct(
        private readonly AuthenticateUser $authenticateUser,
        private LoginUrlGenerator $loginUrlGenerator
    ) {}


    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->loginUrlGenerator->generate()
        );
    }

    public function authenticate(Request $request): Passport
    {

        $session = $request->getSession();
        if ($session && $session->has('keycloak_access_token')) {
            $accessToken = $session->get('keycloak_access_token');

            $domainUser = $this->authenticateUser->execute($accessToken);
            $symfonyUser = new SymfonyUser($domainUser);

            return new SelfValidatingPassport(
                new UserBadge(
                    $symfonyUser->getUserIdentifier(),
                    fn() => $symfonyUser
                )
            );
        }


        if ($request->query->has('code')) {
            return $this->authenticateWithCode($request);
        }

        return $this->authenticateWithBearerToken($request);
    }


    private function authenticateWithCode(Request $request): Passport
    {
        $code = $request->query->get('code');

        if (!$code) {
            throw new AuthenticationException('Authorization code missing');
        }

        $accessToken = $this->authenticateUser->exchangeCodeForToken($code);

        // 🔐 validation JWT
        $domainUser = $this->authenticateUser->execute($accessToken);

        $symfonyUser = new SymfonyUser($domainUser);

        return new SelfValidatingPassport(
            new UserBadge(
                $symfonyUser->getUserIdentifier(),
                fn() => $symfonyUser
            )
        );
    }

    public function authenticateWithBearerToken(Request $request): Passport
    {
        $token = $request->headers->get('Authorization');
        if (empty($token)) {
            throw new AuthenticationException("No Token provided");
        }

        try {
            $domainUser = $this->authenticateUser->execute($token);
        } catch (AuthenticationException $exception) {
            throw $exception;
        }

        $symfonyUser = new SymfonyUser($domainUser);
        return new SelfValidatingPassport(
            new UserBadge(
                $symfonyUser->getUserIdentifier(),
                fn() => $symfonyUser // 🔥 restrict Symfony to search a UserProvider
            )
        );
    }


    public function supports(Request $request): bool
    {


        $isLoginCheckRoute = $request->attributes->get('_route') === 'keycloak_login_check';
        $hasAuthHeader = $request->headers->has('Authorization');

        return $isLoginCheckRoute || $hasAuthHeader;
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
