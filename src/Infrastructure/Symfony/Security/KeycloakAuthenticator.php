<?php

namespace  KeycloakAuthBundle\Infrastructure\Symfony\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use KeycloakAuthBundle\Application\UseCase\AuthenticateUser;
use KeycloakAuthBundle\Application\UseCase\RateLimit;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use KeycloakAuthBundle\Infrastructure\Keycloak\LoginUrlGenerator;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\AccesDeniedEvent;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\LoginValidateEvent;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\TokenValidEvent;
use KeycloakAuthBundle\Infrastructure\Symfony\Models\SymfonyUser;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class KeycloakAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{

    public function __construct(
        private readonly AuthenticateUser $authenticateUser,
        private readonly LoginUrlGenerator $loginUrlGenerator,
        private readonly RateLimit $rateLimit,
        private EventDispatcherInterface $eventDispatcher
    ) {}


    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        // Si c’est une API → 401 JSON
        if (str_starts_with($request->getPathInfo(), '/api')) {
            return new JsonResponse([
                'message' => 'Authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Sinon → redirect Keycloak
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
            $symfonyUser = new SymfonyUser($domainUser, $accessToken);
            $this->rateLimit->execute($domainUser->getId());
            $request->getSession()->set('keycloak_access_token', $accessToken);
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

        if ($request->headers->has('Authorization')) {
            return $this->authenticateWithBearerToken($request);
        }

        throw new AuthenticationException("No authentication method found");
    }


    private function authenticateWithCode(Request $request): Passport
    {
        $code = $request->query->get('code');

        if (!$code) {
            throw new AuthenticationException('Authorization code missing');
        }


        $accessToken = $this->authenticateUser->exchangeCodeForToken($code);
        $request->getSession()->set('keycloak_access_token', $accessToken);



        $domainUser = $this->authenticateUser->execute($accessToken);
        $this->rateLimit->execute($domainUser->getId());
        $symfonyUser = new SymfonyUser($domainUser, $accessToken);



        $this->eventDispatcher->dispatch(
            new TokenValidEvent(
                $symfonyUser->getUserIdentifier(),
                $symfonyUser->getRoles()
            ),
            TokenValidEvent::class
        );
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

        $this->rateLimit->execute($domainUser->getId());
        $symfonyUser = new SymfonyUser($domainUser, $token);

        $this->eventDispatcher->dispatch(
            new TokenValidEvent(
                $symfonyUser->getUserIdentifier(),
                $symfonyUser->getRoles()
            ),
            TokenValidEvent::class
        );
        return new SelfValidatingPassport(
            new UserBadge(
                $symfonyUser->getUserIdentifier(),
                fn() => $symfonyUser // 🔥 restrict Symfony to search a UserProvider
            )
        );
    }




    public function supports(Request $request): bool
    {
        return
            $request->attributes->get('_route') === 'keycloak_login_check'
            || $request->headers->has('Authorization');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {

        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        if (Response::HTTP_UNAUTHORIZED) {
            $this->eventDispatcher->dispatch(
                new AccesDeniedEvent(
                    userId: null,
                    ip: $request->getClientIp(),
                    reason: $exception->getMessageKey()
                ),
                AccesDeniedEvent::class
            );
        } else

            $this->eventDispatcher->dispatch(
                new LoginValidateEvent(
                    ip: $request->getClientIp(),
                    userId: null,
                    reason: $exception->getMessageKey()
                ),
                LoginValidateEvent::class
            );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }
}
