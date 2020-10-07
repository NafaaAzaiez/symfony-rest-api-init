<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Tests\Behat;

use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;

class FeatureContext extends RestContext
{
    /**
     * @var JsonInspector
     */
    private $inspector;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->inspector = new JsonInspector('javascript');
    }

    /**
     * @Then the JSON node :node should be greater than the number :number
     */
    public function theJsonNodeShouldBeGreaterThanTheNumber($node, $number)
    {
        $value = $this->inspector->evaluate(new Json($this->request->getContent()), $node);

        $this->assertTrue($value > $number);
    }

    /**
     * @Then dump the response
     */
    public function dumpTheResponse()
    {
        $response = $this->request->getContent();

        var_dump($response);
    }

    /**
     * @Then the JSON node :node length should be :length
     */
    public function theJsonNodeLengthShouldBeEqualsTo($node, $length)
    {
        $value = $this->inspector->evaluate(new Json($this->request->getContent()), $node);

        $this->assertEquals($length, strlen($value));
    }
}
