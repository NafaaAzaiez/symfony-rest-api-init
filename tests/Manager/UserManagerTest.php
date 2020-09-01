<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Tests\Manager;

use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Manager\UserManager
 */
class UserManagerTest extends TestCase
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManger;

    /**
     * @var ObjectRepository
     */
    private $repository;

    public function setup(): void
    {
        $this->entityManger = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(ObjectRepository::class);
        $this->entityManger->expects($this->once())->method('getRepository')->willReturn($this->repository);
        $this->userManager = new UserManager($this->entityManger);
    }

    /**
     * @covers ::createUser
     */
    public function testCreateUser()
    {
        $email = 'address@email.com';
        $name = 'userName';
        $provider = 'a-provider';

        $user = $this->userManager->createUser($email, $name, $provider);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($name, $user->getFirstName());
        $this->assertEquals($name, $user->getLastName());
        $this->assertEquals($provider, $user->getSignInProvider());
        $this->assertNotEmpty($user->getPassword());
    }

    public function testFindAll()
    {
        $this->repository->expects($this->once())->method('findAll')->willReturn([]);
        $result = $this->userManager->findAll();
        $this->assertEquals([], $result);
    }

    public function testGetEntityManager()
    {
        $this->assertEquals($this->entityManger, $this->userManager->getEntityManager());
    }

    public function testGetRepository()
    {
        $this->assertEquals($this->repository, $this->userManager->getRepository());
    }
}
