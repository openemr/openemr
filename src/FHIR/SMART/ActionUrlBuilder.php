<?php

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ActionUrlBuilder
{
    public function __construct(private readonly SessionInterface $session, private readonly string $actionURL, private readonly string $csrfTokenName = 'csrf_token')
    {
    }

    public function buildUrl(string|array $action, array $options = []): string
    {
        if (\is_array($action)) {
            $action = implode("/", $action);
        }
        $url = $this->actionURL . "?action=" . urlencode($action) . "&csrf_token=" . urlencode((string) $this->getCSRFToken());
        if (!empty($options['queryParams'])) {
            foreach ($options['queryParams'] as $key => $param) {
                $url .= "&" . urlencode((string) $key) . "=" . urlencode((string) $param);
            }
        }

        if (!empty($options['fragment'])) {
            $url .= "#" . $options['fragment'];
        }

        return $url;
    }
    private function getCSRFToken()
    {
        return CsrfUtils::collectCsrfToken($this->csrfTokenName, $this->session);
    }
}
