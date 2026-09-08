<?php

/**
 * Controller Class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR contributors
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) OpenEMR contributors
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\RequestTerminator;
use OpenEMR\Core\ControllerInterface;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Storage\CacheDirectory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as TwigEnvironment;

// TODO: @adunsulag move these into src/
class Controller extends Smarty implements ControllerInterface
{
    /**
     * Valid controller names mapped to class suffixes.
     *
     * TODO: Once controllers move to src/ with PSR-4 autoloading, this can be
     * replaced by checking class_exists() and instanceof ControllerInterface.
     */
    private const VALID_CONTROLLERS = [
        'document' => 'Document',
        'document_category' => 'DocumentCategory',
        'hl7' => 'Hl7',
        'insurance_company' => 'InsuranceCompany',
        'insurance_numbers' => 'InsuranceNumbers',
        'patient_finder' => 'PatientFinder',
        'pharmacy' => 'Pharmacy',
        'practice_settings' => 'PracticeSettings',
        'prescription' => 'Prescription',
        'x12_partner' => 'X12Partner',
    ];

    /**
     * ACL requirements for controllers.
     * Maps controller name to [section, value, display_name].
     */
    private const CONTROLLER_ACL_MAP = [
        'document' => ['patients', 'docs', 'Documents'],
        'document_category' => ['admin', 'practice', 'Practice Settings'],
        'hl7' => ['admin', 'practice', 'Practice Settings'],
        'insurance_company' => ['admin', 'practice', 'Practice Settings'],
        'insurance_numbers' => ['admin', 'practice', 'Practice Settings'],
        // patient_finder's only caller is the document-move destination picker
        // (templates/documents/general_view.html), so it requires the same
        // write-level documents ACL as the reassignment it feeds.
        'patient_finder' => ['patients', 'docs', 'Documents', ['write', 'addonly']],
        'pharmacy' => ['admin', 'practice', 'Practice Settings'],
        'practice_settings' => ['admin', 'practice', 'Practice Settings'],
        'prescription' => ['patients', 'rx', 'Prescriptions'],
        'x12_partner' => ['admin', 'practice', 'Practice Settings'],
    ];

    public string $template_mod;
    /**
     * Redeclared from parent to fix incorrect type info
     * @var string|string[]
     */
    protected $template_dir;
    public string $_current_action; // seems to be unneeded in practice
    public bool $_state;
    public $_args = [];
    protected $form = null;
    protected TwigEnvironment $twig;

    public function __construct(?TwigEnvironment $twig = null)
    {
         parent::__construct();
         $this->twig = $twig ?? ServiceContainer::getTwig();
         $this->template_mod = "general";
         $this->_current_action = "";
         $this->_state = true;
         $this->setCompileDir((new CacheDirectory())->for('openemr-smarty'));
         $this->setCompileCheck(true);
         $this->setPluginsDir([__DIR__ . "/../smarty/plugins", OEGlobalsBag::getInstance()->getKernel()->getVendorDir() . "/smarty/smarty/libs/plugins"]);
         // Register {$x|text} explicitly so Smarty does not fall back to the
         // deprecated unregistered-function path when escaping for text nodes.
         $this->registerPlugin('modifier', 'text', self::escapeForTextNode(...));
         $this->assign("PROCESS", "true");
         $this->assign("HEADER", "<html><head></head><body>");
         $this->assign("FOOTER", "</body></html>");
         $this->assign("CONTROLLER", "controller.php?");
         $this->assign("CONTROLLER_THIS", "controller.php?" . ($_SERVER['QUERY_STRING'] ?? ''));
         $this->assign('GLOBALS', $GLOBALS);
    }

    public function set_current_action(string $action)
    {
         $this->_current_action = $action;
    }

    public function default_action(): string
    {
         return "<html><body></body></html>";
    }

    public function process_action()
    {
         echo $this->default_action();
    }

    public function populate_object(&$obj): bool
    {
        if (!is_object($obj)) {
            $this->function_argument_error();
        }

        foreach ($_POST as $varname => $var) {
            $varname = preg_replace("/[^A-Za-z0-9_]/", "", (string) $varname);
            $func = "set_" . $varname;
            if ((!(str_starts_with("_", (string) $varname))) && is_callable([$obj,$func])) {
                //echo "c: $func on w: "  . $var . "<br />";

                $obj->$func($var, $_POST);
            }
        }

            return true;
    }

    public function function_argument_error(): never
    {
         $body = $this->twig->render("error/" . $this->template_mod . "_function_argument.html.twig");
         (new RequestTerminator())->error(400, $body);
    }

    public function i_once($file)
    {
         return include_once($file);
    }

    /**
     * Check ACL for a controller and deny access if not authorized.
     */
    protected function checkControllerAcl(string $controllerName): void
    {
        if (!isset(self::CONTROLLER_ACL_MAP[$controllerName])) {
            // Fail closed. Every controller reachable through dispatch() must
            // declare an explicit ACL requirement; adding an entry to
            // VALID_CONTROLLERS without a corresponding CONTROLLER_ACL_MAP entry
            // must not silently skip authorization.
            $this->throwAccessDenied(
                "No ACL mapping defined for controller: $controllerName",
                xl('Access Denied')
            );
        }

        [$section, $value, $displayName] = self::CONTROLLER_ACL_MAP[$controllerName];
        $returnValue = self::CONTROLLER_ACL_MAP[$controllerName][3] ?? '';
        if (!AclMain::aclCheckCore($section, $value, '', $returnValue)) {
            $this->throwAccessDenied(
                "ACL check failed for $section/$value: $displayName",
                xl($displayName)
            );
        }
    }

    /**
     * Log/audit an ACL denial and throw an HTTP 403 exception for controller flow.
     */
    protected function throwAccessDenied(
        string $comment,
        string $message,
        string $auditEvent = 'security-access-denied'
    ): never {
        AccessDeniedHelper::logDenial($comment, $auditEvent);
        throw new AccessDeniedHttpException($message);
    }

    /**
     * Legacy routing that extracts controller/action from parameter order.
     *
     * @deprecated Use dispatch() instead for order-independent routing.
     * @param non-empty-array<string|int, mixed> $qarray
     */
    public function act(array $qarray): string
    {
        // Extract controller (first key) and action (second key) from positional params
        $keys = array_keys($qarray);
        $controller = (string) $keys[0];
        $positionalAction = (string) ($keys[1] ?? 'default');

        // Build params for dispatch()
        $params = ['controller' => $controller, 'action' => $positionalAction];

        // Add remaining params (those not used for positional routing)
        foreach ($qarray as $key => $value) {
            if ($key === $controller || $key === $positionalAction) {
                continue;
            }
            // Rename explicit 'action' to avoid collision with routing action.
            // This preserves the sub-action value (e.g., 'list' in action=list)
            // for sub-controller delegation patterns like practice_settings->pharmacy->list.
            $params[$key === 'action' ? 'sub_action' : $key] = $value;
        }

        return $this->dispatch($params);
    }

    /**
     * Dispatch to a controller action using explicit 'controller' and 'action' parameters.
     *
     * This method provides order-independent routing. URLs should use:
     *   controller.php?controller=document&action=view&patient_id=123&doc_id=456
     *
     * @param array<string|int, mixed> $params Query parameters (typically $_GET)
     * @return string Controller output
     */
    public function dispatch(array $params): string
    {
        // Look up controller in whitelist
        $controllerParam = is_string($params['controller'] ?? null) ? $params['controller'] : '';
        if (!isset(self::VALID_CONTROLLERS[$controllerParam])) {
            throw new BadRequestHttpException("Missing or invalid 'controller' parameter");
        }
        $className = "C_" . self::VALID_CONTROLLERS[$controllerParam];

        // ACL check
        $this->checkControllerAcl($controllerParam);

        // Handle process flag
        if (isset($params['process'])) {
            unset($_GET['process']);
            unset($params['process']);
            $_POST['process'] = "true";
        }

        // Load controller file
        $controllerFile = OEGlobalsBag::getInstance()->getProjectDir() . "/controllers/$className.class.php";
        if (!$this->i_once($controllerFile)) {
            throw new NotFoundHttpException("Unable to load controller: $className");
        }

        // Instantiate controller
        $controllerObj = new $className();

        // Build action method name
        $action = is_string($params['action'] ?? null) ? $params['action'] : 'default';
        $actionMethod = "{$action}_action";
        $processMethod = "{$actionMethod}_process";

        $controllerObj->_current_action = $action;

        // Build arguments array from remaining params (excluding controller, action, process)
        $reservedParams = ['controller', 'action', 'process'];
        $args = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $reservedParams, true)) {
                continue;
            }
            // Pass null for empty values (matching act() behavior)
            $args[$key] = $value === '' ? null : $value;
        }

        // Call the action method
        $output = "";

        $callMethod = function (string $method) use ($controllerObj, $args): string {
            $result = $controllerObj->$method(...array_values($args));
            return is_string($result) ? $result : '';
        };

        $isProcessing = ($_POST['process'] ?? '') === 'true';

        if ($isProcessing && $this->methodExists($controllerObj, $processMethod)) {
            $output .= $callMethod($processMethod);
            if ($controllerObj->_state === false) {
                return $output;
            }
        }

        if ($this->methodExists($controllerObj, $actionMethod)) {
            $output .= $callMethod($actionMethod);
        } else {
            throw new NotFoundHttpException("Action '$action' does not exist on controller: $className");
        }

        return $output;
    }

    /**
     * Check whether a method genuinely exists on a controller object.
     *
     * Smarty 4's __call makes is_callable() always return true on
     * Smarty-derived objects, so we must also check method_exists().
     */
    private function methodExists(object $controllerObj, string $method): bool
    {
        return method_exists($controllerObj, $method)
            && is_callable([$controllerObj, $method]);
    }

    private static function escapeForTextNode(mixed $value): string
    {
        if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || $value instanceof \Stringable) {
            return \text((string) $value);
        }
        return '';
    }

    public function _link($action = "default", $inlining = false)
    {
         $url_parts = explode("&", (string) $_SERVER['REQUEST_URI']);
         $link = array_shift($url_parts);
         //print_r($url_parts);

        if (!str_contains($url_parts[0], "=")) {
            $inline_arg = $url_parts[0];
            $url_parts[0] = $action;
        } else {
            array_unshift($url_parts, $action);
        }

        if ($inlining) {
            $link .= "&" . urlencode($inline_arg);
            $link .= "&action=" . urlencode((string) $url_parts[0]);
        } else {
            $link .= "&" . urlencode((string) $url_parts[0]);
        }

        foreach ($this->_args as $arg_name => $arg) {
            $link .= "&" . urlencode((string) $arg_name) . "=" . urlencode((string) $arg);
        }

            $link .= "&";
            return  $link;
    }
}
