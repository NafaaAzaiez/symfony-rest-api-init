<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Tests\Normalizer;

use App\Normalizer\CustomConstraintViolationListNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CustomConstraintViolationListNormalizerTest extends TestCase
{
    /**
     * @var CustomConstraintViolationListNormalizer
     */
    private $normalizer;

    public function setup(): void
    {
        $this->normalizer = new CustomConstraintViolationListNormalizer();
    }

    public function testNormalize()
    {
        $message = 'This is a violation';
        $propertyPath = 'propertyPath';
        $violation = new ConstraintViolation($message, null, ['test'], 'originValue', $propertyPath, 'invalidValue');

        $response = $this->normalizer->normalize(new ConstraintViolationList([$violation]));

        $this->assertIsArray($response);
        $this->assertEquals(['code' => 400, 'message' => sprintf('%s: %s', $propertyPath, $message)], $response);
    }
}
