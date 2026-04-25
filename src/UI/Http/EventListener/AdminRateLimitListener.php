<?php

declare(strict_types=1);

namespace App\UI\Http\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
final class AdminRateLimitListener
{
    public function __construct(
        private readonly RateLimiterFactory $adminLoginLimiter,
        private readonly Security $security,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/admin')) {
            return;
        }

        if (!$request->headers->has('Authorization')) {
            return;
        }

        if (null !== $this->security->getUser()) {
            return;
        }

        $limiter = $this->adminLoginLimiter->create($request->getClientIp() ?? 'anonymous');

        if (!$limiter->consume()->isAccepted()) {
            $event->setResponse(new Response('Trop de tentatives. Réessayez plus tard.', 429));
        }
    }
}
