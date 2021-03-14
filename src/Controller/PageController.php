<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class PageController
{
    public function about()
    {
        // HTML Integration
        ob_start();
        include __DIR__ . '/../pages/info/about.php';

        // Return Response
        return new Response(ob_get_clean());
    }
}