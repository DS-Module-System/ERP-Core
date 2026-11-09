<?php

/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-18
 * Time: 14:20
 */

namespace App\Menu;

use App\Service\Core\DomainTranslationService;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Builder
{
    private FactoryInterface $factory;
    private DomainTranslationService $domainTranslationService;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        FactoryInterface $factory,
        DomainTranslationService $domainTranslationService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->factory = $factory;
        $this->domainTranslationService = $domainTranslationService;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * createMainMenu
     * creates the left navigation menu
     *
     * @param array<mixed> $options
     * 
     * @return ItemInterface
     */
    public function createMainMenu(array $options): ItemInterface
    {

        $menu = $this->factory->createItem('root');
        // ----------------------------------- //


        //Added roles respectively to the left menu

        // --- Dashboard (everyone with ROLE_USER) ---
        //maybe everyone
        // if ($this->isAllowed(['ROLE_USER'])) {
        $menu->addChild('core.leftMenu.dashboard', [
            'label' => $this->domainTranslationService->translate('core.leftMenu.dashboard'),
            'route' => 'dashboard',
        ]);
        // }

        // --- Administration ---
        $administration = $menu->addChild('core.leftMenu.administration', [
            'label' => $this->domainTranslationService->translate('core.leftMenu.administration'),
        ]);

        if ($this->isAllowed(['ROLE_USER_VIEW', 'ROLE_USER_GROUP_VIEW'])) {
            $administration->addChild('user.leftMenu.users', [
                'label' => $this->domainTranslationService->translate('user.leftMenu.users'),
                'route' => 'user_list',
                'extras' => [
                    'routes' => ['user_create', 'user_edit', 'user_change_password'],
                ],
            ]);
        }

        if ($this->isAllowed(['ROLE_USER_VIEW', 'ROLE_USER_GROUP_VIEW'])) {
            $administration->addChild('user.leftMenu.userGroups', [
                'label' => $this->domainTranslationService->translate('user.leftMenu.userGroups'),
                'route' => 'user_group_list',
                'extras' => [
                    'routes' => ['user_group_edit', 'user_group_create'],
                ],
            ]);
        }

        //should be allowed to be seen by everyone
        $administration->addChild('core.leftMenu.information', [
            'label' => $this->domainTranslationService->translate('core.leftMenu.information'),
            'route' => 'core_information',
        ]);


        return $menu;
    }


    private function isAllowed(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }
        return false;
    }
}
