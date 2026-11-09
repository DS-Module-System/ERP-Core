<?php

namespace App\Service\User;

use Symfony\Component\Yaml\Yaml;

class RoleLoader
{
    private string $roleDir;

    public function __construct(string $projectDir)
    {
        $this->roleDir = $projectDir . '/config/roles';
    }

    public function loadRoles(): array
    {
        $groups = [];

        foreach (glob($this->roleDir . '/*.yaml') as $file) {

            $basename = basename($file, '.yaml'); // delivery, client, invoice...
            $data = Yaml::parseFile($file);

            // The real roles are inside:
            // parameters:
            //      <basename>_roles: { ... }
            $paramKey = $basename . '_roles';

            if (!isset($data['parameters'][$paramKey])) {
                continue; // skip files that don't follow expected format
            }

            $roles = $data['parameters'][$paramKey];

            // Turn "delivery" → "Delivery"
            $groupName = ucfirst($basename);

            $groups[$groupName] = $roles;
        }

        return $groups;
    }
}
