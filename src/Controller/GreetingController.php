<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GreetingController
{
    public function hello($name)
    {
        // HTML Integration
        ob_start();
        include __DIR__ . '/../pages/hello.php';

        // Return Response
        return new Response(ob_get_clean());
    }

    public function bye()
    {
        // HTML Integration
        ob_start();
        include __DIR__ . '/../pages/bye.php';

        // Return Response
        return new Response(ob_get_clean());
    }
}