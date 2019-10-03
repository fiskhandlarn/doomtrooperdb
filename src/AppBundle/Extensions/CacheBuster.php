<?php

namespace AppBundle\Extensions;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CacheBuster
 * @package AppBundle\Extensions
 */
class CacheBuster extends \Twig_Extension
{
    public function __construct(RequestStack $requestStack)
    {
        if ($requestStack && $requestStack->getCurrentRequest()) {
            $this->rootDir = $requestStack->getCurrentRequest()->server->get('DOCUMENT_ROOT');
            $this->rootURL = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        }
    }

    public function asset(string $path): string
    {
        $file = $this->rootDir . $path;
        $url = $this->rootURL . $path;

        if (file_exists($file)) {
            $mtime = filemtime($file);
            if ($mtime !== false) {
                $url .= '?v=' . base_convert($mtime, 10, 36);
            }
        }

        return $url;
    }

    public function getFunctions()
    {
        return [new \Twig_SimpleFunction('cached_asset', [$this, 'asset'])];
    }
}
