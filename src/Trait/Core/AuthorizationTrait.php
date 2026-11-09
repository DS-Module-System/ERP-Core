<?php
namespace App\Trait\Core;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait AuthorizationTrait
{
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected UrlGeneratorInterface $urlGenerator;

    public function setAuthorizationDependencies(
        AuthorizationCheckerInterface $authorizationChecker,
        UrlGeneratorInterface $urlGenerator
    ): void {
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator = $urlGenerator;
    }

    protected function isAllowed(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }

        throw new AccessDeniedException();
    }

    protected function requireRoles(array $roles, string $redirectRoute = 'dashboard'): ?RedirectResponse
    {
        try {
            $this->isAllowed($roles);
        } catch (AccessDeniedException $e) {
            if (property_exists($this, 'translator') && $this->translator instanceof TranslatorInterface) {
                $this->addFlash('error', $this->translator->trans('core.layout.permissionsError'));
            } else {
                $this->addFlash('error', 'Access denied');
            }

            return new RedirectResponse($this->urlGenerator->generate($redirectRoute));
        }

        return null;
    }

    protected function requireApiRoles(array $roles): ?JsonResponse
    {
        try {
            $this->isAllowed($roles);
        } catch (AccessDeniedException $e) {
            $message = property_exists($this, 'translator') && $this->translator instanceof TranslatorInterface
                ? $this->translator->trans('core.layout.permissionsError')
                : 'Access denied';

            return new JsonResponse(['error' => $message], 403);
        }

        return null;
    }
}

