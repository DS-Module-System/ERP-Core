<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RoleHierarchyCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Събираме всички роли от параметрите
        $allRoles = $this->collectAllRoles($container->getParameterBag());

        // Debug информация
        if (!empty($allRoles)) {
            // Добавяме всички роли към ROLE_SUPER_ADMIN
            $this->updateSecurityConfig($container, $allRoles);
        }
    }

    /**
     * Събира всички роли от всички модули
     */
    private function collectAllRoles(ParameterBagInterface $parameterBag): array
    {
        $allRoles = [];
        
        // Автоматично откриваме всички параметри, които завършват с '_roles'
        $allParameters = $parameterBag->all();
        
        foreach ($allParameters as $paramName => $paramValue) {
            if (str_ends_with($paramName, '_roles') && is_array($paramValue)) {
                foreach ($paramValue as $role => $description) {
                    $allRoles[] = $role;
                }
            }
        }

        return $allRoles;
    }

    /**
     * Обновява security конфигурацията
     */
    private function updateSecurityConfig(ContainerBuilder $container, array $allRoles): void
    {
        // Добавяме всички роли към ROLE_SUPER_ADMIN чрез prependExtensionConfig
        $roleHierarchy = [
            'ROLE_SUPER_ADMIN' => array_merge(['ROLE_ADMIN'], $allRoles)
        ];
        // $roleHierarchy =  $allRoles;
        $definition = $container->getDefinition('security.role_hierarchy');
        $definition->setArgument(0, $roleHierarchy);
        // dump($roleHierarchy);
        // $container->prependExtensionConfig('security.role_hierarchy', [
        //     $roleHierarchy
        // ]);
    }
} 