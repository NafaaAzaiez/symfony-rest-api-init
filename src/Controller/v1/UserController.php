<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Controller\v1;

use App\Entity\User;
use App\Manager\UserManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractFOSRestController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * AuthController constructor.
     */
    public function __construct(ValidatorInterface $validator, UserManager $userManager)
    {
        $this->validator = $validator;
        $this->userManager = $userManager;
    }

    /**
     * @SWG\Tag(name="User")
     * @Nelmio\Security(name="Bearer")
     *
     * @SWG\Get(
     *     summary="Returns the profile of the user with the provided id",
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the profile of the user with the provided id",
     *     @Nelmio\Model(type=User::class, groups={"user"})
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="JWT Token not found"
     * )
     *
     * @Rest\Get("/users/{id}", requirements={"id"="\d+"})
     */
    public function getUserById(User $user)
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['id', 'user']]);
    }

    /**
     * Returns the profile of the connected user.
     *
     * @SWG\Tag(name="User")
     * @Nelmio\Security(name="Bearer")
     *
     * @SWG\Get(
     *     summary="Returns the profile of the connected user",
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the profile of the connected user",
     *     @Nelmio\Model(type=User::class, groups={"user"})
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="JWT Token not found"
     * )
     *
     * @Rest\Get("/users/me")
     */
    public function getConnectedUserProfile()
    {
        return $this->json($this->getUser(), Response::HTTP_OK, [], ['groups' => ['id', 'user']]);
    }

    /**
     * @SWG\Tag(name="User")
     * @Nelmio\Security(name="Bearer")
     *
     * @SWG\Patch(
     *     summary="Patch a user",
     * )
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Nelmio\Model(type=User::class, groups={"patchUser"})
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Patch a user",
     *     @Nelmio\Model(type=User::class, groups={"user"})
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="JWT Token not found"
     * )
     *
     * @Rest\Patch("/users/{id}")
     */
    public function patchUser(User $user, Request $request, ObjectNormalizer $objectNormalizer)
    {
        if ($this->getUser()->getId() !== $user->getId()) {
            throw new BadRequestHttpException('You can not patch a user different from the connected user');
        }
        $input = json_decode($request->getContent(), true);

        $objectNormalizer->denormalize(
            $input,
            User::class,
            'json',
            [
                AbstractObjectNormalizer::GROUPS => 'patchUser',
                AbstractObjectNormalizer::OBJECT_TO_POPULATE => $user,
                AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            ]
        );

        if (count($errors = $this->validator->validate($user, null, ['patch']))) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->userManager->getEntityManager()->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user', 'id']]);
    }
}
