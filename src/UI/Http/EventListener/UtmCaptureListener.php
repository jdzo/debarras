<?php

declare(strict_types=1);

namespace App\UI\Http\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(event: 'kernel.request', priority: 100)]
final class UtmCaptureListener
{
    private const UTM_PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
    private const SESSION_KEY = 'utm_tracking';

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        if ($session->has(self::SESSION_KEY)) {
            return;
        }

        $utm = [];
        foreach (self::UTM_PARAMS as $param) {
            $value = $request->query->get($param);
            if ($value !== null && $value !== '') {
                $utm[$param] = self::sanitize($value);
            }
        }

        if ($utm === []) {
            return;
        }

        $referrer = $request->headers->get('referer');
        $utm['referrer'] = $referrer !== null ? self::sanitizeUrl($referrer) : null;
        $utm['landing_page'] = self::sanitizeUrl($request->getUri());

        $session->set(self::SESSION_KEY, $utm);
    }

    private static function sanitize(string $value): string
    {
        $value = strip_tags($value);
        $value = str_replace(["\r", "\n", "\t"], '', $value);
        return mb_substr($value, 0, 200);
    }

    private static function sanitizeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return null;
        }

        return mb_substr(strip_tags($url), 0, 500);
    }
}
