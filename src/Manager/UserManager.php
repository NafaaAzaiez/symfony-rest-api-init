<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * UserManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(User::class);
    }

    /**
     * @throws \Exception
     */
    public function createUser(string $email, string $name, string $provider): User
    {
        return (new User())
            ->setEmail($email)
            ->setUsername($email)
            ->setPassword(base64_encode(random_bytes(10)))
            ->setSignInProvider($provider)
            ->setFirstName($name)
            ->setLastName($name);
    }

    public function findAll()
    {
        return $this->repository->findAll();
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function getRepository(): ObjectRepository
    {
        return $this->repository;
    }
}
