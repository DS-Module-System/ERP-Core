<?php


namespace App\Service\Core;

use App\Entity\User\BaseUser;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Condition\TwoFactorConditionInterface;

class TwoFactorCondition implements TwoFactorConditionInterface
{
    public function shouldPerformTwoFactorAuthentication(AuthenticationContextInterface $context): bool
    {
        /** @var BaseUser $user */
        $user = $context->getUser();
        return $user->getIsEnableTwoFa();
    }
}
