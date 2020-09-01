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
use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase\Auth;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AuthManager
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var JWTTokenManagerInterface
     */
    private $tokenManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var Auth
     */
    private $firebaseAuth;

    /**
     * AuthManager constructor.
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, EntityManagerInterface $entityManager, JWTTokenManagerInterface $tokenManager, UserManager $userManager, NormalizerInterface $normalizer, Auth $firebaseAuth)
    {
        $this->encoderFactory = $encoderFactory;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
        $this->userManager = $userManager;
        $this->normalizer = $normalizer;
        $this->firebaseAuth = $firebaseAuth;
    }

    public function registerUser(User $user): User
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        $encryptedPass = $encoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($encryptedPass);
        $user->setUsername($user->getEmail());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $user->setToken($this->generateToken($user));

        return $user;
    }

    public function generateToken(User $user): string
    {
        return $this->tokenManager->create($user);
    }

    /**
     * Take a firebase token, validate it, fetch existing user or create a new user
     * Then return an array with app token to be used for authentication and the user.
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function loginFirebase(string $firebaseToken, ?string $messagingToken = null): User
    {
        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($firebaseToken);
        } catch (InvalidToken $e) {
            throw new BadRequestHttpException('The firebase token is invalid');
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException('The firebase token could not be parsed');
        }

        // Collect user data
        $uid = $verifiedIdToken->getClaim('sub');
        $firebaseUser = $this->firebaseAuth->getUser($uid);

        if (is_null($firebaseUser->email)) {
            throw new BadRequestHttpException('There is no email address linked to your account');
        }
        try {
            $signInProvider = $firebaseUser->providerData[0]->providerId;
        } catch (\Exception $e) {
            $signInProvider = 'firebase';
        }

        // Check if user already exist
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $firebaseUser->email]);

        // In case of new user, create and register with random password and firebase name as first and last name
        if (!$user) {
            $user = $this->userManager->createUser($firebaseUser->email, $firebaseUser->displayName, $signInProvider);
            $this->registerUser($user);
        } else {
            $user->setToken($this->generateToken($user));
        }

        if (!is_null($messagingToken)) {
            $user->setMessagingToken($messagingToken);
            $this->entityManager->flush();
        }

        return $user;
    }

    public function logout(User $user)
    {
        $user->setToken(null);
        $user->setMessagingToken(null);
        $this->entityManager->flush();
    }
}
