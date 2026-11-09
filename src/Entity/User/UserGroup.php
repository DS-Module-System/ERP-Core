<?php

namespace App\Entity\User;

use App\Repository\User\UserGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserGroupRepository::class)]
class UserGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    protected ?string $name = null;

    #[ORM\ManyToMany(targetEntity: BaseUser::class, mappedBy: 'userGroups')]
    private Collection $baseUsers;

    /**
     * @var  array<string> $roles
     */
    #[ORM\Column]
    protected ?array $roles = null;

    public function __construct()
    {
        $this->baseUsers = new ArrayCollection();
        $this->roles = [];
    }
    /**
     * {@inheritdoc}
     */
    public function addRole(string $role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = strtoupper($role);
        }

        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * {@inheritdoc}
     */
    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    /**
     * @return  array<string>
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }
    /**
     * {@inheritdoc}
     */
    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, BaseUser>
     */
    public function getBaseUsers(): Collection
    {
        return $this->baseUsers;
    }

    public function addBaseUser(BaseUser $baseUser): static
    {
        if (!$this->baseUsers->contains($baseUser)) {
            $this->baseUsers->add($baseUser);
            $baseUser->addUserGroup($this);
        }

        return $this;
    }

    public function removeBaseUser(BaseUser $baseUser): static
    {
        if ($this->baseUsers->removeElement($baseUser)) {
            $baseUser->removeUserGroup($this);
        }

        return $this;
    }


}
