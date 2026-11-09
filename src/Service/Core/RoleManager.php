<?php

namespace App\Service\Core;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RoleManager
{
    private array $allRoles = [];
    private array $roleHierarchy = [];

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->collectAllRoles($parameterBag);
        $this->buildRoleHierarchy();
    }

    /**
     * Събира всички роли от всички модули
     */
    private function collectAllRoles(ParameterBagInterface $parameterBag): void
    {
        // Автоматично откриваме всички параметри, които завършват с '_roles'
        $allParameters = $parameterBag->all();
        
        foreach ($allParameters as $paramName => $paramValue) {
            if (str_ends_with($paramName, '_roles') && is_array($paramValue)) {
                foreach ($paramValue as $role => $description) {
                    $this->allRoles[$role] = $description;
                }
            }
        }
    }

    /**
     * Изгражда йерархията на ролите
     */
    private function buildRoleHierarchy(): void
    {
        // Основни роли
        $this->roleHierarchy = [
            'ROLE_USER' => [],
            'ROLE_ADMIN' => ['ROLE_USER'],
            'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
        ];

        // Добавяме всички специфични роли към ROLE_SUPER_ADMIN
        $allSpecificRoles = array_keys($this->allRoles);
        $this->roleHierarchy['ROLE_SUPER_ADMIN'] = array_merge(
            $this->roleHierarchy['ROLE_SUPER_ADMIN'],
            $allSpecificRoles
        );
    }

    /**
     * Връща всички роли с техните описания
     */
    public function getAllRoles(): array
    {
        return $this->allRoles;
    }

    /**
     * Връща ролите групирани по модули
     */
    public function getRolesByModule(): array
    {
        $groupedRoles = [];
        
        foreach ($this->allRoles as $role => $description) {
            $module = $this->getModuleFromRole($role);
            if (!isset($groupedRoles[$module])) {
                $groupedRoles[$module] = [];
            }
            $groupedRoles[$module][$role] = $description;
        }

        return $groupedRoles;
    }

    /**
     * Връща йерархията на ролите за security.yaml
     */
    public function getRoleHierarchy(): array
    {
        return $this->roleHierarchy;
    }

    /**
     * Определя модула от името на ролята
     */
    private function getModuleFromRole(string $role): string
    {
        if (str_starts_with($role, 'ROLE_USER_')) {
            return 'User Management';
        }
        if (str_starts_with($role, 'ROLE_INVOICE_')) {
            return 'Invoice Management';
        }
        if (in_array($role, ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER', 'ROLE_ACCOUNT'])) {
            return 'Core System';
        }
        
        return 'Other';
    }

    /**
     * Връща ролите за конкретен модул
     */
    public function getRolesForModule(string $module): array
    {
        $groupedRoles = $this->getRolesByModule();
        return $groupedRoles[$module] ?? [];
    }

    /**
     * Проверява дали ролята съществува
     */
    public function hasRole(string $role): bool
    {
        return array_key_exists($role, $this->allRoles) || 
               in_array($role, ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
    }

    /**
     * Връща описанието на ролята
     */
    public function getRoleDescription(string $role): ?string
    {
        return $this->allRoles[$role] ?? null;
    }
} 