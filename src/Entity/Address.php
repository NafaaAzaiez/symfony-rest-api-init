<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 */
class Address extends AbstractEntity
{
    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $street;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $number;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $city;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $zipCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"user", "listUser","patchUser"})
     */
    protected $instructions;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="user", cascade={"all"})
     */
    protected $user;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zipCode;
    }

    public function setZipCode(?int $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
