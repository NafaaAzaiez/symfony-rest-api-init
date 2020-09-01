<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Normalizer;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;

class CustomConstraintViolationListNormalizer extends ConstraintViolationListNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);

        return ['code' => Response::HTTP_BAD_REQUEST, 'message' => $result['detail'] ?? ''];
    }
}
