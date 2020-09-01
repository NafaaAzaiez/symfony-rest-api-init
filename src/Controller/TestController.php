<?php

/*
 * This file is part of the open source project symfony-rest-api-init.
 *
 * It is made public and available for any use you want by its creator Nafaa Azaiez.
 * For any question or suggestion please send an email at azaiez.nafaa@gmail.com
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * THIS CONTROLLER IS USED TO TEST FIREBASE LOGIN.
 *
 * TODO: AFTER TESTING, REMOVE THIS FILE ALONG WITH THE FOLLOWING:
 *      - ROUTES CONFIG (config/routes/annotation.yaml)
 *      - templates/oauth.html.twig
 */
class TestController extends AbstractController
{
    /**
     * Front page to allow google and facebook sign-in in order to get a firebase token
     * The firebase token is to be sent on the route /login/firebase.
     *
     * @Route("/oauth")
     */
    public function oauth()
    {
        return $this->render('oauth.html.twig');
    }
}
