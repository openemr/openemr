<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Model;

use Laminas\Json\Json;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

class JsonModel extends ViewModel
{
    /**
     * JSON probably won't need to be captured into a
     * a parent container by default.
     *
     * @var string
     */
    protected $captureTo = null;

    /**
     * JSONP callback (if set, wraps the return in a function call)
     *
     * @var string
     */
    protected $jsonpCallback = null;

    /**
     * JSON is usually terminal
     *
     * @var bool
     */
    protected $terminate = true;

    /**
     * Set the JSONP callback function name
     *
     * @param  string $callback
     * @return JsonModel
     */
    public function setJsonpCallback($callback)
    {
        $this->jsonpCallback = $callback;
        return $this;
    }

    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        $variables = $this->getVariables();
        if ($variables instanceof Traversable) {
            $variables = ArrayUtils::iteratorToArray($variables);
        }

        $options = [
            'prettyPrint' => $this->getOption('prettyPrint'),
        ];

        if (null !== $this->jsonpCallback) {
            return $this->jsonpCallback.'('.Json::encode($variables, false, $options).');';
        }
        return Json::encode($variables, false, $options);
    }
}
