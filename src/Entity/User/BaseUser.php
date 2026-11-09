<?php

namespace App\Entity\User;

use App\Repository\User\BaseUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: BaseUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class BaseUser implements PasswordAuthenticatedUserInterface, TwoFactorInterface, UserInterface {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    protected ?string $email = null;

    /**
     * @var  array<string> $roles
     */
    #[ORM\Column]
    protected array $roles = [];

    #[ORM\Column(length: 255)]
    protected ?string $password = null;

    #[ORM\Column]
    protected ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    protected ?string $authCode = null;

    #[ORM\Column]
    protected ?bool $isEnableTwoFa = null;

    #[ORM\Column]
    protected ?int $trustedVersion = null;

    #[ORM\Column(nullable: true)]
    protected ?bool $isDarkTheme = null;

    #[ORM\Column(length: 60)]
    protected ?string $name = null;

    #[ORM\Column(length: 60)]
    protected ?string $family = null;

    #[ORM\ManyToMany(targetEntity: UserGroup::class, inversedBy: 'baseUsers')]
    protected Collection $userGroups;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $isVerified = true;


    public function __construct() {
        $this->userGroups = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): self {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string {
        return (string) $this->email;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string {
        return (string) $this->password;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive(): ?bool {
        return $this->isActive;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive(?bool $isActive): self {
        $this->isActive = $isActive;

        return $this;
    }

    public function isEmailAuthEnabled(): bool {
        return true; // This can be a persisted field to switch email code authentication on/off
    }

    public function getEmailAuthRecipient(): string {
        return $this->email;
    }

    public function getEmailAuthCode(): string {
        if (null === $this->authCode) {
            throw new \LogicException('The email authentication code was not set');
        }

        return $this->authCode;
    }

    public function setEmailAuthCode(string $authCode): void {
        $this->authCode = $authCode;
    }

    public function getIsEnableTwoFa(): ?bool {
        return $this->isEnableTwoFa;
    }

    public function setIsEnableTwoFa(bool $isEnableTwoFa): self {
        $this->isEnableTwoFa = $isEnableTwoFa;

        return $this;
    }

    public function setTrustedTokenVersion(int $trustedVersion): self {
        $this->trustedVersion = $trustedVersion;
        return $this;
    }

    public function getTrustedTokenVersion(): int {
        return $this->trustedVersion;
    }

    public function isIsDarkTheme(): ?bool {
        return $this->isDarkTheme;
    }

    public function setIsDarkTheme(?bool $isDarkTheme): self {
        $this->isDarkTheme = $isDarkTheme;

        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getFamily(): ?string {
        return $this->family;
    }

    public function setFamily(string $family): self {
        $this->family = $family;

        return $this;
    }

    public function getFullName(): string {
        return $this->getName() . ' ' . $this->getFamily();
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array {
        $roles = [];
        foreach ($this->getUserGroups() as $group) {
            foreach ($group->getRoles() as $role) {
                $roles[] = $role;
            }
        }
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroups(): Collection {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): static {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): static {
        $this->userGroups->removeElement($userGroup);

        return $this;
    }

    public function getIsVerified(): bool {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static {
        $this->isVerified = $isVerified;

        return $this;
    }
}
