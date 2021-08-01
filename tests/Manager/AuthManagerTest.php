<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Tests\Manager;

use App\Entity\User;
use App\Manager\AuthManager;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\UserRecord;
use Lcobucci\JWT\Claim\Basic;
use Lcobucci\JWT\Token;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @coversDefaultClass \App\Manager\AuthManager
 *
 * We will be using typed properties here introduced in PHP 7.4
 */
class AuthManagerTest extends TestCase
{
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    private EntityManagerInterface $entityManager;

    private JWTTokenManagerInterface $tokenManager;

    private UserManager $userManager;

    private NormalizerInterface $normalizer;

    private Auth $firebaseAuth;

    private AuthManager $authManager;

    private ObjectRepository $repository;

    public function setup(): void
    {
        $this->passwordHasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tokenManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->userManager = $this->createMock(UserManager::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->firebaseAuth = $this->createMock(Auth::class);
        $this->repository = $this->createMock(ObjectRepository::class);

        $this->authManager = new AuthManager($this->passwordHasherFactory, $this->entityManager, $this->tokenManager, $this->userManager, $this->normalizer, $this->firebaseAuth);
    }

    /**
     * @group fail
     * @covers ::registerUser
     */
    public function testRegisterUser()
    {
        // Prepare mocks and expected calls
        $password = 'password';
        $email = 'address@email.com';
        $token = '$Token$';
        $encryptedPass = '$ThisIsAHashedPassword$';
        $user = (new User())
            ->setEmail($email)
            ->setPassword($password);
        $passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->passwordHasherFactory->expects($this->once())
            ->method('getPasswordHasher')
            ->with($user)
            ->willReturn($passwordHasher);

        $passwordHasher->expects($this->any())->method('hash')->with($password)->willReturn($encryptedPass);
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');
        $this->tokenManager->expects($this->once())->method('create')->willReturn($token);

        // Call the manager function
        $this->authManager->registerUser($user);

        // Check the result
        $this->assertEquals($encryptedPass, $user->getPassword());
        $this->assertEquals($email, $user->getUsername());
        $this->assertEquals($token, $user->getToken());
    }

    /**
     * @covers ::loginFirebase
     */
    public function testLoginFirebaseExceptionInvalidToken()
    {
        $firebaseToken = 'firebase-token';
        $this->firebaseAuth->expects($this->once())
            ->method('verifyIdToken')
            ->with($firebaseToken)
            ->willThrowException(new InvalidToken(new Token()));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('The firebase token is invalid');

        $this->authManager->loginFirebase($firebaseToken);
    }

    /**
     * @covers ::loginFirebase
     */
    public function testLoginFirebaseExceptionInvalidArgumentException()
    {
        $firebaseToken = 'firebase-token';
        $this->firebaseAuth->expects($this->once())
            ->method('verifyIdToken')
            ->with($firebaseToken)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('The firebase token could not be parsed');

        $this->authManager->loginFirebase($firebaseToken);
    }

    /**
     * @covers ::loginFirebase
     */
    public function testLoginFirebaseNoEmail()
    {
        $token = new Token([], ['sub' => new Basic('sub', 'a-value')]);
        $firebaseToken = 'firebase-token';
        $firebaseUser = new UserRecord();
        $this->firebaseAuth->expects($this->once())
            ->method('verifyIdToken')
            ->with($firebaseToken)
            ->willReturn($token);
        $this->firebaseAuth->expects($this->once())
            ->method('getUser')
            ->willReturn($firebaseUser);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('There is no email address linked to your account');

        $this->authManager->loginFirebase($firebaseToken);
    }

    public function testLoginFirebaseNewUser()
    {
        $email = 'address@gmail.com';
        $password = 'password';
        $encryptedPass = '$encrypted$';
        $finalToken = '$Token$';
        $token = new Token([], ['sub' => new Basic('sub', 'a-value')]);
        $firebaseToken = 'firebase-token';
        $firebaseUser = new UserRecord();
        $firebaseUser->email = $email;
        $firebaseUser->displayName = 'name';
        $user = (new User())
            ->setEmail($email)
            ->setPassword($password);
        $this->firebaseAuth->expects($this->once())
            ->method('verifyIdToken')
            ->with($firebaseToken)
            ->willReturn($token);
        $this->firebaseAuth->expects($this->once())
            ->method('getUser')
            ->willReturn($firebaseUser);

        $this->repository->expects($this->once())
            ->method('findOneBy');

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->userManager->expects($this->once())
            ->method('createUser')
            ->willReturn($user);

        $this->tokenManager->expects($this->once())
            ->method('create')
            ->willReturn($finalToken);

        $passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('hash')
            ->with($password)
            ->willReturn($encryptedPass);

        $this->passwordHasherFactory->expects($this->once())
            ->method('getPasswordHasher')
            ->willReturn($passwordHasher);

        $result = $this->authManager->loginFirebase($firebaseToken);

        // Check the result
        $this->assertEquals($encryptedPass, $user->getPassword());
        $this->assertEquals($email, $user->getUsername());
        $this->assertEquals($finalToken, $user->getToken());
        $this->assertEquals($finalToken, $user->getToken());
    }

    public function testLoginFirebaseExistentUserWithMessagingToken()
    {
        $email = 'address@gmail.com';
        $encryptedPass = '$encrypted$';
        $finalToken = '$Token$';
        $token = new Token([], ['sub' => new Basic('sub', 'a-value')]);
        $firebaseToken = 'firebase-token';
        $firebaseUser = new UserRecord();
        $firebaseUser->email = $email;
        $firebaseUser->displayName = 'name';
        $existentUser = (new User())
            ->setEmail($email)
            ->setUsername($email)
            ->setPassword($encryptedPass);
        $this->firebaseAuth->expects($this->once())
            ->method('verifyIdToken')
            ->with($firebaseToken)
            ->willReturn($token);
        $this->firebaseAuth->expects($this->once())
            ->method('getUser')
            ->willReturn($firebaseUser);

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($existentUser);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->userManager->expects($this->never())
            ->method('createUser');

        $this->tokenManager->expects($this->once())
            ->method('create')
            ->willReturn($finalToken);

        $passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $passwordHasher->expects($this->never())
            ->method('hash');

        $this->passwordHasherFactory->expects($this->never())
            ->method('getPasswordHasher');

        $messagingToken = '$messagingToken$';
        $result = $this->authManager->loginFirebase($firebaseToken, $messagingToken);

        // Check the result
        $this->assertEquals($encryptedPass, $existentUser->getPassword());
        $this->assertEquals($email, $existentUser->getUsername());
        $this->assertEquals($finalToken, $existentUser->getToken());
        $this->assertEquals($finalToken, $existentUser->getToken());
        $this->assertEquals($messagingToken, $existentUser->getMessagingToken());
    }

    /**
     * @covers ::generateToken
     */
    public function testGenerateToken()
    {
        $token = '$Token$';
        $this->tokenManager->expects($this->once())->method('create')->willReturn($token);

        $this->assertEquals($token, $this->authManager->generateToken(new User()));
    }

    /**
     * @covers ::logout
     */
    public function testLogout()
    {
        $user = (new User())
            ->setToken('token')
            ->setMessagingToken('messageToken');

        $this->entityManager->expects($this->once())->method('flush');

        $this->authManager->logout($user);

        $this->assertNull($user->getToken());
        $this->assertNull($user->getMessagingToken());
    }
}
