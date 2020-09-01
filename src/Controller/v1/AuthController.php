<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Controller\v1;

use App\Entity\User;
use App\Manager\AuthManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractFOSRestController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * AuthController constructor.
     */
    public function __construct(ValidatorInterface $validator, AuthManager $authManager)
    {
        $this->validator = $validator;
        $this->authManager = $authManager;
    }

    /**
     * @SWG\Tag(name="Register")
     *
     * @SWG\Post(
     *     summary="Register a new user",
     * )
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Nelmio\Model(type=User::class, groups={"user"})
     * )
     *
     * @SWG\Response(
     *     response=201,
     *     description="The newly created user",
     *     @Nelmio\Model(type=User::class, groups={"user"})
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Invalid input data for the user registration"
     * )
     *
     * @Rest\Post("/register")
     *
     * @ParamConverter(name="user", converter="fos_rest.request_body")
     */
    public function register(User $user)
    {
        if (count($errors = $this->validator->validate($user, null, ['register']))) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        $this->authManager->registerUser($user);

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user', 'id']]);
    }

    /**
     * @SWG\Tag(name="Authentication")
     *
     * @Security(name="Bearer")
     *
     * @SWG\Post(
     *     summary="Logout the connected user",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Logout successfully"
     * )
     *
     * @Rest\Post("/logout")
     */
    public function logout()
    {
        $this->authManager->logout($this->getUser());

        return $this->json(['code' => 0]);
    }

    /**
     * This function takes a firebase token, check if user exist in data base or create it
     * Then generate an app token to be used for authentication afterward.
     *
     * It takes also an optional parameter 'messagingToken' which can contain
     * the unique mobile device token to be used afterward to send notifications.
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Post(
     *     summary="Login a user with firebase",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the rewards of an user"
     * )
     *
     * @Rest\Post("/login/firebase")
     *
     * @Rest\RequestParam(name="firebaseToken", nullable=false, strict=true)
     * @Rest\RequestParam(name="messagingToken", nullable=true, strict=true)
     */
    public function loginFirebase(ParamFetcherInterface $paramFetcher)
    {
        $firebaseToken = $paramFetcher->get('firebaseToken');
        $messagingToken = $paramFetcher->get('messagingToken');

        $user = $this->authManager->loginFirebase($firebaseToken, $messagingToken);

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user', 'id']]);
    }
}
