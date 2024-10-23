<?php

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Csrf\CsrfUtils;

class ActionUrlBuilder
{
    public function __construct(private string $actionURL, private string $csrfTokenName = 'csrf_token')
    {
    }

    public function buildUrl(string|array $action, array $options = []): string
    {
        if (\is_array($action)) {
            $action = implode("/", $action);
        }
        $url = $this->actionURL . "?action=" . urlencode($action) . "&csrf_token=" . urlencode($this->getCSRFToken());
        if (!empty($options['queryParams'])) {
            foreach ($options['queryParams'] as $key => $param) {
                $url .= "&" . urlencode($key) . "=" . urlencode($param);
            }
        }

        if (!empty($options['fragment'])) {
            $url .= "#" . $options['fragment'];
        }

        return $url;
    }
    private function getCSRFToken()
    {
        return CsrfUtils::collectCsrfToken($this->csrfTokenName);
    }
}
