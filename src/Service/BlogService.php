<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\HttpFoundation\Request;

#[AsRoutingConditionService(alias: 'route_checker')]
class BlogService
{
    public function check(Request $request): bool
    {
        $userAgent = $request->headers->get('User-Agent');

        if (strpos($userAgent, 'PostmanRuntime') !== false) {
            return true;
        }

        return false;
    }
}   