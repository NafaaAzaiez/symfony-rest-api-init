<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"email"}, groups={"register"})
 */
class User extends AbstractEntity implements UserInterface
{
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"register", "patch"})
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"register", "patch"})
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank(groups={"register", "patch"})
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $phone;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date",nullable=true)
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $birthdate;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Groups({"user", "listUser"})
     *
     * @Assert\NotBlank(groups={"register", "patch"})
     * @Assert\Email(groups={"register", "patch"})
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"register", "patch"})
     * @Assert\Length(min="6", minMessage="password.min.length", groups={"register", "patch"})
     */
    protected $password;

    /**
     * @var string
     *
     * Used only as input to validate password
     */
    protected $checkPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $civility;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $signInProvider = 'inAppRegister';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"user", "listUser"})
     */
    protected $valid = true;

    /**
     * @var Address|null
     *
     * @ORM\OneToOne(targetEntity=Address::class, mappedBy="user", cascade={"all"})
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $address;

    /**
     * @var string|null
     *
     * @Groups("user")
     */
    protected $token;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $messagingToken;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $roles = [self::ROLE_USER];

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getSignInProvider(): string
    {
        return $this->signInProvider;
    }

    public function setSignInProvider(string $signInProvider): self
    {
        $this->signInProvider = $signInProvider;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getMessagingToken(): ?string
    {
        return $this->messagingToken;
    }

    public function setMessagingToken(?string $messagingToken): self
    {
        $this->messagingToken = $messagingToken;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;
        if ($address) {
            $address->setUser($this);
        }

        return $this;
    }

    public function getCheckPassword(): string
    {
        return $this->checkPassword;
    }

    public function setCheckPassword(string $checkPassword): self
    {
        $this->checkPassword = $checkPassword;

        return $this;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTime $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * This is a calculated field.
     *
     * @SerializedName("age")
     *
     * @Groups({"user", "listUser"})
     *
     * @SWG\Property(property="age", type="number", example=29)
     */
    public function getAge()
    {
        if (!is_null($this->birthdate)) {
            return $this->birthdate->diff(new \DateTime())
                ->y;
        }

        return null;
    }

    /**
     * @Assert\Callback(groups={"register"})
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->password !== $this->checkPassword) {
            $context->buildViolation('Check password should be the same as the password')
                ->atPath('password')
                ->addViolation();
        }
    }
}
