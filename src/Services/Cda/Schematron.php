<?php

/**
 * Implementation of ISO Schematron (http://www.schematron.com) validator with
 * Schematron 1.5 back compatibility.
 *
 * Class do not require XSLT extension nor additional XSLT documents. It works
 * with DOM extension only. It is a main purpose of this implementation.
 *
 * Schema validity is not checked completly, only self important things like
 * ID references and required attributes are checked. So, you should pass valid
 * schema.
 *
 * Presence of <sch:schema> is not required, so you can validate schematron in
 * Relax NG documents. But set ALLOW_MISSING_SCHEMA_ELEMENT option to enable it.
 *
 * Not implemented elements: let, diagnostic, diagnostics, dir, emph, flag, fpi,
 * icon, p, role, see, span, subject. Almost all of them are for documentation
 * purpose. Open issue on repository if you wish to be implemented.
 *
 * Example of usage:
 * <code>
 * use OpenEMR\Services\Cda\Schematron;
 *
 * $validator = new Schematron(Schematron::NS_ISO);
 * $validator->load('personal-schema.sch');
 *
 * $doc = new DOMDocument;
 * $doc->load($xmlDocument);
 *
 * $result = $validator->validate($doc, Schematron::RESULT_COMPLEX);
 * var_dump($result);
 * </code>
 *
 * You can choose one of four licences:
 *
 * @licence  New BSD License
 * @licence  GNU General Public License version 2
 * @licence  GNU General Public License version 3
 * @licence  MIT
 *
 * @version  >
 * @see      https://github.com/milo/schematron
 *
 * @author   Miloslav Hůla (https://github.com/milo)
 */

/**
 * Integration Implementation and additions by
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use OpenEMR\Services\Cda\SchematronHelpers as Helpers;

use DOMDocument,
    DOMElement,
    DOMNode,
    DOMNodeList,
    DOMXPath;

use ErrorException,
    InvalidArgumentException,
    RuntimeException,
    stdClass;

class Schematron
{
    /** Class version */
    const
        VERSION = '1.0.0';

    /** Namespace of supported schematron versions */
    const
        NS_DETECT = null,
        NS_ISO = 'http://purl.oclc.org/dsdl/schematron',
        NS_1_5 = 'http://www.ascc.net/xml/schematron';

    /** Type of {@link self::validate()} return value */
    const
        RESULT_SIMPLE = 'simple',
        RESULT_COMPLEX = 'complex',
        RESULT_EXCEPTION = 'exception';

    /** Standardized validation phase */
    const
        PHASE_ALL = '#ALL',
        PHASE_DEFAULT = '#DEFAULT';

    /** Type of include URIs for {@link self::setAllowedInclude()} */
    const
        INCLUDE_URL = 0x01,
        INCLUDE_ABSOLUTE_PATH = 0x02,
        INCLUDE_RELATIVE_PATH = 0x04,
        INCLUDE_ALL = 0xFF;


    const
        /** Default options */
        DEFAULT_OPTIONS = 0x0000,

        /** Allow missing <sch:schema> (useful for Relax NG) */
        ALLOW_MISSING_SCHEMA_ELEMENT = 0x0001,

        /** Ignore <sch:include>, do not expand them */
        IGNORE_INCLUDE = 0x0002,

        /** Forbid <sch:include>, do not allow them */
        FORBID_INCLUDE = 0x0004,

        /** Skip <sch:rule> with same context as any rule before */
        SKIP_DUPLICIT_RULE_CONTEXT = 0x0008,

        /** Allow to <sch:schema> do not contain <sch::pattern> */
        ALLOW_EMPTY_SCHEMA = 0x0010,

        /** Allow to <sch:pattern> do not contain <sch:rule> */
        ALLOW_EMPTY_PATTERN = 0x0020,

        /** Allow to <sch:rule> do not contain <sch:assert> nor <sch:report> */
        ALLOW_EMPTY_RULE = 0x0040;


    /** XPath class used in this class */
    public static $xPathClass = 'OpenEMR\Services\Cda\SchematronXPath';

    /** @var bool  schema has been loaded */
    private $loaded = false;

    /** @var int */
    private $options = self::DEFAULT_OPTIONS;

    /** @var string  schema namespace */
    private $ns;

    /** @var string|NULL  absolute path for <sch:include> relative paths */
    private $directory;

    /** @var int  LibXML options which were used for schema loading */
    private $domOptions;

    /** @var string|NULL  loaded from @schemaVersion in <sch:schema> */
    private $version;

    /** @var string|NULL  loaded from <sch:title> in <sch:schema> */
    private $title;

    /** @var string  default validation phase */
    private $defaultPhase = self::PHASE_ALL;

    /** @var int|FALSE|NULL  restrictions on <sch:include>; self::INCLUDE_* value/mask */
    private $allowedInclude = self::INCLUDE_RELATIVE_PATH;

    /** @var int  how deep can be <sch:include> */
    private $maxIncludeDepth = 10;


    /** @var SchematronXPath */
    protected $xPath;

    /** @var array[prefix => URI]  loaded from <sch:ns> */
    protected $namespaces = array();

    /** @var stdClass[]  {@see self::findPatterns()} */
    protected $patterns = array();

    /** @var array[id => value]  {@see self::findPhases()} */
    protected $phases = array();


    /**
     * @param string  schema namespace (self::NS_*)
     * @throws InvalidArgumentException  when unsupported namespace passed
     */
    public function __construct($namespace = self::NS_DETECT)
    {
        if (!in_array($namespace, array(self::NS_DETECT, self::NS_ISO, self::NS_1_5), true)) {
            throw new InvalidArgumentException("Unsupported schema namespace '$namespace'.");
        }

        $this->ns = $namespace;
    }


    /**
     * Loads schematron schema from file.
     *
     * @param string  path/URI to schema file
     * @param int  LibXML options
     * @throws SchematronException  when schema loading fails
     */
    public function load($file, $options = null)
    {
        $this->domOptions = $options === null ? (LIBXML_NOENT | LIBXML_NOBLANKS) : $options;

        $doc = new DOMDocument();
        Helpers::handleXmlErrors();
        $doc->load($file, $this->domOptions);
        if ($e = Helpers::fetchXmlErrors()) {
            throw new SchematronException("Cannot load schema from file '$file'.", 0, $e);
        }

        if (is_file($file)) {
            $this->directory = dirname(realpath($file));
        }

        return $this->loadDom($doc);
    }


    /**
     * Loads schematron schema from DOMDocument.
     *
     * @return self
     * @throws SchematronException  when schema loading fails
     * @throws RuntimeException  when <sch:include> expanding fails
     */
    public function loadDom(DOMDocument $schema)
    {
        if ($this->ns === self::NS_DETECT) {
            $this->ns = $schema->getElementsByTagNameNS(self::NS_ISO, '*')->length
                ? self::NS_ISO
                : self::NS_1_5;
        }

        $this->expandIncludes($schema);

        $this->xPath = new self::$xPathClass($schema);
        $this->xPath->registerNamespace('sch', $this->ns);

        $this->loadSchemaBasics($schema);
        $this->namespaces = $this->findNamespaces($schema);
        $this->patterns = $this->findPatterns($schema);
        if (!count($this->patterns) && !($this->options & self::ALLOW_EMPTY_SCHEMA)) {
            throw new SchematronException('None <sch:pattern> found in schema.');
        }
        $this->phases = $this->findPhases($schema);

        $this->loaded = true;

        return $this;
    }


    /**
     * Validate document over against loaded schema.
     *
     * @param DOMDocument  document to validate
     * @param string  type of return value
     * @param string  validation phase
     * @return array
     * @throws RuntimeException  when schema has not been loaded yet
     * @throws InvalidArgumentException  when validation $phase is not defined
     * @throws SchematronException  when $result is RESULT_EXCEPTION and document is not valid
     */
    public function validate(DOMDocument $doc, $result = self::RESULT_SIMPLE, $phase = self::PHASE_DEFAULT)
    {
        if (!$this->loaded) {
            throw new RuntimeException('Schema has not been loaded yet. Load it before validation.');
        }

        $xpath = new self::$xPathClass($doc);
        foreach ($this->namespaces as $prefix => $uri) {
            $xpath->registerNamespace($prefix, $uri);
        }

        if ($phase === self::PHASE_DEFAULT) {
            $phase = $this->defaultPhase;
        }

        if ($phase === self::PHASE_ALL) {
            $activePatternKeys = array_keys($this->patterns);
        } elseif (!array_key_exists($phase, $this->phases)) {
            throw new InvalidArgumentException("Validation phase '$phase' is not defined.");
        } else {
            $activePatternKeys = array_keys($this->phases[$phase]);
        }

        $return = array();
        foreach ($activePatternKeys as $patternKey) {
            $pattern = $this->patterns[$patternKey];
            foreach ($pattern->rules as $ruleKey => $rule) {
                foreach ($xpath->queryContext($rule->context, $doc) as $currentNode) {
                    foreach ($rule->statements as $statement) {
                        if ($statement->isAssert ^ $xpath->evaluate("boolean($statement->test)", $currentNode)) {
                            $message = $this->statementToMessage($statement->node, $xpath, $currentNode);

                            switch ($result) {
                                case self::RESULT_EXCEPTION:
                                    throw new SchematronException($message);

                                case self::RESULT_COMPLEX:
                                    if (!isset($return[$patternKey])) {
                                        $return[$patternKey] = (object)array(
                                            'title' => $pattern->title,
                                            'rules' => array(),
                                        );
                                    }

                                    if (!isset($return[$patternKey]->rules[$ruleKey])) {
                                        $return[$patternKey]->rules[$ruleKey] = (object)array(
                                            'context' => $rule->context,
                                            'errors' => array(),
                                        );
                                    }

                                    $return[$patternKey]->rules[$ruleKey]->errors[] = (object)array(
                                        'test' => $statement->test,
                                        'message' => $message,
                                        'path' => $currentNode->getNodePath(),
                                    );
                                    break;

                                default:
                                    $return[] = $message;
                                    break;
                            }
                        } // test
                    } // statements for context
                } // context elements
            } // rules
        } // patterns

        return $return;
    }


    /**
     * Returns version loaded from @schemaVersion on <sch:schema>
     *
     * @return string|NULL
     */
    public function getSchemaVersion()
    {
        return $this->version;
    }


    /**
     * Returns title loaded from <sch:title> in <sch:schema>
     *
     * @return string|NULL
     */
    public function getSchemaTitle()
    {
        return $this->title;
    }


    /**
     * Set processing options, {@link self::DEFAULT_OPTIONS}
     *
     * @param int  mask of options
     * @return self
     */
    public function setOptions($options = self::DEFAULT_OPTIONS)
    {
        $this->options = $options;
        return $this;
    }


    /**
     * Returns processing options, {@link self::DEFAULT_OPTIONS}
     *
     * @return int
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * Has been schema loaded?
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->loaded;
    }


    /**
     * Set which URIa are allowed for <sch:include> (self::INCLUDE_*)
     *
     * @param int  mask of types
     * @return self
     */
    public function setAllowedInclude($mask)
    {
        $this->allowedInclude = $mask;
        return $this;
    }


    /**
     * Returns which URIa are allowed for <sch:include> (self::INCLUDE_*)
     *
     * @return int
     */
    public function getAllowedInclude()
    {
        return $this->allowedInclude;
    }


    /**
     * Sets how deep can be <sch:include> in <sch:include> in <sch:include> ...
     *
     * @param int  depth
     * @return self
     */
    public function setMaxIncludeDepth($depth)
    {
        $this->maxIncludeDepth = (int)$depth;
        return $this;
    }


    /**
     * Returns how deep can be <sch:include> in <sch:include> in <sch:include> ...
     *
     * @param int  depth
     * @return self
     */
    public function getMaxIncludeDepth()
    {
        return $this->maxIncludeDepth;
    }


    /**
     * Sets include directory path for relative file paths in <sch:include>
     *
     * @param string  directory path
     * @return self
     * @throws RuntimeException  when directory does not exist
     */
    public function setIncludeDir($dir)
    {
        if (!is_dir($dir)) {
            throw new RuntimeException("Directory '$dir' does not exist.");
        }
        $this->directory = realpath($dir);

        return $this;
    }


    /**
     * Returns path to directory which is used for relative file paths from <sch:include>
     *
     * @return string|NULL
     */
    public function getIncludeDir()
    {
        return $this->directory;
    }



    /* ~~~ Schematron schema loading part ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    /**
     * Expands all <sch:include> in DOM.
     *
     * @param DOMElement
     * @param int  include depth level
     * @throws SchematronException
     * @throws RuntimeException  when applied any include restriction
     */
    protected function expandIncludes(DOMDocument $schema, $depth = 0)
    {
        if ($this->options & self::IGNORE_INCLUDE) {
            return;
        }

        if ($depth > $this->maxIncludeDepth) {
            throw new RuntimeException("Reached maximum ($this->maxIncludeDepth) include depth.");
        }

        $list = $schema->getElementsByTagNameNS($this->ns, 'include');
        if ($list->length > 0 && ($this->options & self::FORBID_INCLUDE)) {
            throw new RuntimeException("Include functionality is disabled. Found $list->length <{$list->item(0)->nodeName}> elements, first on line {$list->item(0)->getLineNo()}.");
        }

        while ($list->length) { // do not foreach(), list is affected by replaceChild
            $element = $list->item(0);

            $href = $rawHref = Helpers::getAttribute($element, 'href');
            if (substr_compare($href, 'file://', 0, 7, true) === 0) {
                $href = substr($href, 7);
            }

            $type = static::detectIncludeType($href, $typeStr);

            if (!($this->allowedInclude & $type)) {
                throw new RuntimeException("Including URI of type '$typeStr' referenced by <$element->nodeName> on line {$element->getLineNo()} is not allowed.");
            }

            if ($type === self::INCLUDE_RELATIVE_PATH) {
                if ($this->directory === null) {
                    throw new RuntimeException("Cannot evaluate relative URI '$rawHref' referenced by <$element->nodeName> on line {$element->getLineNo()}, schema has not been loaded from file. Set schema directory by setIncludeDir() method.");
                }
                $href = $this->directory . DIRECTORY_SEPARATOR . $href;
            }

            $doc = new DOMDocument();
            Helpers::handleXmlErrors();
            $doc->load($href, $this->domOptions);
            if ($e = Helpers::fetchXmlErrors()) {
                throw new RuntimeException("Cannot load '$rawHref' referenced by <$element->nodeName> on line {$element->getLineNo()}.", 0, $e);
            }

            $this->expandIncludes($doc, $depth + 1);

            $element->parentNode->replaceChild(
                $schema->importNode($doc->documentElement, true),
                $element
            );
        }
    }


    /**
     * Fills object members by basics schema properties.
     *
     * @throws SchematronException
     */
    protected function loadSchemaBasics(DOMDocument $schema)
    {
        $list = $this->xPath->query('//sch:schema', $schema);
        if ($list->length > 1) {
            throw new SchematronException("Only one <schema> element in document is allowed, but $list->length found.");
        } elseif ($list->length < 1) {
            if (!($this->options & self::ALLOW_MISSING_SCHEMA_ELEMENT)) {
                throw new SchematronException('<schema> element not found.');
            }
        } else {
            $element = $list->item(0);

            $this->version = Helpers::getAttribute($element, 'schemaVersion', null);
            $this->defaultPhase = Helpers::getAttribute($element, 'defaultPhase', self::PHASE_ALL);
            if (strtolower($binding = Helpers::getAttribute($element, 'queryBinding', 'xslt')) !== 'xslt') {
                throw new SchematronException("Query binding '$binding' is not supported.");
            }

            $titleElements = $this->xPath->query('sch:title', $element);
            if ($titleElements->length > 0) {
                $this->title = $titleElements->item(0)->textContent;
            }
        }
    }


    /**
     * Search for all <sch:ns>.
     *
     * @return array[string prefix => string URI]
     * @throws SchematronException
     */
    protected function findNamespaces(DOMDocument $schema)
    {
        $namespaces = $elements = array();
        foreach ($this->xPath->query('//sch:ns', $schema) as $element) {
            $prefix = Helpers::getAttribute($element, 'prefix');
            $uri = Helpers::getAttribute($element, 'uri');

            if (array_key_exists($prefix, $elements)) {
                throw new SchematronException("Namespace prefix '$prefix' on line {$element->getLineNo()} is alredy declared on line {$elements[$prefix]->getLineNo()}.");
            }

            $elements[$prefix] = $element;
            $namespaces[$prefix] = $uri;
        }
        return $namespaces;
    }


    /**
     * Search for all <sch:pattern>. Abstract patterns are instantized.
     *
     * @return stdClass[]
     * @throws SchematronException
     */
    protected function findPatterns(DOMDocument $schema)
    {
        $abstracts = $this->findPatternAbstracts($schema);

        $patterns = array();
        foreach ($this->xPath->query('//sch:pattern[not(@abstract) or @abstract!="true"]', $schema) as $element) {
            if (($isA = Helpers::getAttribute($element, 'is-a', null)) !== null) {
                if (!array_key_exists($isA, $abstracts)) {
                    throw new SchematronException("<$element->nodeName> on line {$element->getLineNo()} references to undefined abstract pattern by ID '$isA'.");
                }
                $pattern = $this->instantiatePattern($abstracts[$isA], $this->findParams($element));
            } else {
                $pattern = (object)array(
                    'title' => $this->xPath->evaluate('boolean(sch:title)', $element)
                        ? $this->xPath->evaluate('string(sch:title)', $element)
                        : Helpers::getAttribute($element, 'name', null), // Schematron v1.5
                    'rules' => $rules = $this->findRules($element),
                );

                if (!count($rules) && !($this->options & self::ALLOW_EMPTY_PATTERN)) {
                    throw new SchematronException("Missing rules for <$element->nodeName> on line {$element->getLineNo()}.");
                }
            }
            $pattern->id = Helpers::getAttribute($element, 'id', null);

            if ($pattern->id === null) {
                $patterns[] = $pattern;
            } else {
                $patterns["#$pattern->id"] = $pattern;
            }
        }

        return $patterns;
    }


    /**
     * Search for all <sch:pattern abstract="TRUE">
     *
     * @return array[id => stdClass]
     * @throws SchematronException
     */
    protected function findPatternAbstracts(DOMDocument $schema)
    {
        $patterns = array();
        foreach ($this->xPath->query('//sch:pattern[@abstract="true"]', $schema) as $element) {
            if ($element->hasAttribute('is-a')) {
                throw new SchematronException("An abstract <$element->nodeName> on line {$element->getLineNo()} shall not have a 'is-a' attribute.");
            }

            $id = Helpers::getAttribute($element, 'id');
            $patterns[$id] = (object)array(
                'title' => $this->xPath->evaluate('boolean(sch:title)', $element)
                    ? $this->xPath->evaluate('string(sch:title)', $element)
                    : Helpers::getAttribute($element, 'name', null), // Schematron v1.5
                'rules' => $rules = $this->findRules($element),
            );

            if (!count($rules) && !($this->options & self::ALLOW_EMPTY_PATTERN)) {
                throw new SchematronException("Missing rules for <$element->nodeName> on line {$element->getLineNo()}.");
            }
        }
        return $patterns;
    }


    /**
     * Returns callable for replacing parameters in XPath expressions.
     *
     * @return  callable(string $expression, array $parameters)
     */
    protected function getReplaceCb()
    {
        static $replaceCb;

        if ($replaceCb === null) {
            $replaceCb = function ($expression, $parameters) {
                foreach ($parameters as $name => $value) {
                    $expression = str_replace("\$$name", $value, $expression);
                }

                return $expression;
            };
        }
        return $replaceCb;
    }


    /**
     * Creates pattern instance from abstract pattern.
     *
     * @param stdClass  abstract pattern
     * @param array[name => value]  parameters
     * @return stdClass
     */
    private function instantiatePattern(stdClass $abstract, array $parameters)
    {
        $instance = clone $abstract;
        foreach ($instance->rules as & $rule) {
            $rule = clone $rule;
            $rule->context = call_user_func($this->getReplaceCb(), $rule->context, $parameters);
            foreach ($rule->statements as & $stmt) {
                $stmt = clone $stmt;
                $stmt->test = call_user_func($this->getReplaceCb(), $stmt->test, $parameters);
            }
        }
        return $instance;
    }


    /**
     * Search for all <sch:param>.
     *
     * @return array[string name => string value]
     * @throws SchematronException
     */
    protected function findParams(DOMElement $pattern)
    {
        $params = $elements = array();
        foreach ($this->xPath->query('sch:param', $pattern) as $element) {
            $name = Helpers::getAttribute($element, 'name');
            $value = Helpers::getAttribute($element, 'value');

            if (array_key_exists($name, $elements)) {
                throw new SchematronException("Parameter '$name' is already defined on line {$elements[$name]->getLineNo()}.");
            }

            $elements[$name] = $element;
            $params[$name] = $value;
        }
        return $params;
    }


    /**
     * Search for all <sch:rule>.
     *
     * @return stdClass[]
     * @throws SchematronException
     */
    protected function findRules(DOMElement $pattern)
    {
        $abstracts = $this->findRuleAbstracts($pattern);

        $rules = $contexts = array();
        foreach ($this->xPath->query('sch:rule[not(@abstract) or @abstract!="true"]', $pattern) as $element) {
            $context = Helpers::getAttribute($element, 'context');

            if (array_key_exists($context, $contexts) && ($this->options & self::SKIP_DUPLICIT_RULE_CONTEXT)) {
                continue;
            }
            $contexts[$context] = true;

            $rules[] = (object)array(
                'context' => $context,
                'statements' => $statements = $this->findStatements($element, $abstracts),
            );

            if (!count($statements) && !($this->options & self::ALLOW_EMPTY_RULE)) {
                throw new SchematronException("Asserts nor reports not found for <$element->nodeName> on line {$element->getLineNo()}.");
            }
        }
        return $rules;
    }


    /**
     * Search for all <sch:rule abstract="TRUE">.
     *
     * @return stdClass[]
     * @throws SchematronException
     */
    protected function findRuleAbstracts(DOMElement $pattern)
    {
        $rules = array();
        foreach ($this->xPath->query('sch:rule[@abstract="true"]', $pattern) as $element) {
            $id = Helpers::getAttribute($element, 'id');
            if ($element->hasAttribute('context')) {
                throw new SchematronException("An abstract rule on line {$element->getLineNo()} shall not have a 'context' attribute.");
            }

            $rules[$id] = (object)array(
                'statements' => $this->findStatements($element),
            );
        }
        return $rules;
    }


    /**
     * Search for all <sch:assert> and <sch:report>.
     *
     * @return stdClass[]
     * @throws SchematronException
     */
    protected function findStatements(DOMElement $rule, array $abstractRules = array())
    {
        $statements = array();
        foreach ($this->xPath->query('sch:assert | sch:report | sch:extends', $rule) as $node) {
            if ($node->localName === 'extends') {
                $idRule = Helpers::getAttribute($node, 'rule');
                if (!isset($abstractRules[$idRule])) {
                    throw new SchematronException("<$node->nodeName> on line {$node->getLineNo()} references to undefined abstract rule by ID '$idRule'.");
                }

                $statements = array_merge($statements, $abstractRules[$idRule]->statements);
            } else {
                $statements[] = (object)array(
                    'test' => Helpers::getAttribute($node, 'test'),
                    'isAssert' => $node->localName === 'assert',
                    'node' => $node,
                );
            }
        }
        return $statements;
    }


    /**
     * Search for all <sch:phase> and check existency of defaultPhase if set in <sch:schema>.
     *
     * @return array[id => array[idPattern]]
     * @throws SchematronException
     */
    protected function findPhases(DOMDocument $schema)
    {
        $phases = $elements = array();
        foreach ($this->xPath->query('//sch:phase', $schema) as $element) {
            $id = Helpers::getAttribute($element, 'id');
            if (isset($elements[$id])) {
                throw new SchematronException("<$element->nodeName> with id '$id' is already defined on line {$elements[$id]->getLineNo()}.");
            }
            $elements[$id] = $element;
            $phases[$id] = $this->findActives($element);
        }

        if ($this->defaultPhase !== self::PHASE_ALL && !array_key_exists($this->defaultPhase, $phases)) {
            throw new SchematronException("Default validation phase '$this->defaultPhase' is not defined.");
        }

        return $phases;
    }


    /**
     * Search for all <sch:active>.
     *
     * @return string[]  list of <sch:pattern> IDs
     * @throws SchematronException
     */
    protected function findActives(DOMElement $phase)
    {
        $actives = array();
        foreach ($this->xPath->query('sch:active', $phase) as $element) {
            $idPattern = Helpers::getAttribute($element, 'pattern');
            if (!isset($this->patterns["#$idPattern"])) {
                throw new SchematronException("<$element->nodeName> on line {$element->getLineNo()} references to undefined pattern by ID '$idPattern'.");
            }
            $actives["#$idPattern"] = $idPattern;
        }
        return $actives;
    }


    /**
     * Expands <sch:name> and <sch:value-of> in assertion/report message.
     *
     * @return string
     */
    protected function statementToMessage(DOMElement $stmt, SchematronXPath $xPath, DOMNode $current)
    {
        $message = '';
        foreach ($stmt->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE && $node->namespaceURI === $this->ns) {
                if ($node->localName === 'name') {
                    $message .= $xPath->evaluate('name(' . Helpers::getAttribute($node, 'path', '') . ')', $current);
                } elseif ($node->localName === 'value-of') {
                    $message .= $xPath->evaluate('string(' . Helpers::getAttribute($node, 'select') . ')', $current);
                } else {
                    /** @todo warning? */
                    $message .= $node->textContent;
                }
            } else {
                $message .= $node->textContent;
            }
        }

        $message = preg_replace('#\s+#', ' ', trim($message));

        return $message;
    }


    /**
     * Detects include URI type.
     *
     * @return int
     */
    protected static function detectIncludeType($uri, &$typeStr = null)
    {
        $absolutePathRe = substr_compare(PHP_OS, 'WIN', 0, 3, true) === 0
            ? '#^[A-Z]:#i'
            : '#^/#';

        if (preg_match('#^[a-z-]+://#i', $uri)) {
            $type = self::INCLUDE_URL;
            $typeStr = 'URL';
        } elseif (preg_match($absolutePathRe, $uri)) {
            $type = self::INCLUDE_ABSOLUTE_PATH;
            $typeStr = 'Absolute file path';
        } else {
            $type = self::INCLUDE_RELATIVE_PATH;
            $typeStr = 'Relative file path';
        }

        return $type;
    }
}


/**
 * Helpers for work with LibXML and DOM.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class SchematronHelpers
{
    /** @var array */
    private static $handleXmlErrors = array();


    /**
     * Enable LibXML internal error handling.
     *
     * @param bool  clear existing errors
     */
    public static function handleXmlErrors($clear = true)
    {
        self::$handleXmlErrors[] = libxml_use_internal_errors(true);
        $clear && libxml_clear_errors();
    }


    /**
     * Fetch all LibXML errors.
     *
     * @param bool
     * @return NULL|ErrorException  all errors chained in exceptions
     */
    public static function fetchXmlErrors($restoreHandling = true)
    {
        $e = null;
        foreach (array_reverse(libxml_get_errors()) as $error) {
            $e = new ErrorException(trim($error->message), $error->code, $error->level, $error->file, $error->line, $e);
        }
        libxml_clear_errors();
        $restoreHandling && self::restoreErrorHandling();
        return $e;
    }


    /**
     * Restore LibXML internal error handling previously enabled by self::handleXmlErrors()
     */
    public static function restoreErrorHandling()
    {
        libxml_use_internal_errors(array_pop(self::$handleXmlErrors));
    }


    /**
     * Returns value of element attribute.
     *
     * @param DOMElement
     * @param string  attribute name
     * @param mixed  default value if attribude does not exist
     * @return mixed
     * @throws SchematronException  when attribute does not exist and default value is not specified
     */
    public static function getAttribute(DOMElement $element, $name)
    {
        if ($element->hasAttribute($name)) {
            return $element->getAttribute($name);
        } elseif (count($args = func_get_args()) > 2) {
            return $args[2];
        }

        throw new SchematronException("Missing required attribute '$name' for element <$element->nodeName> on line {$element->getLineNo()}.");
    }
}


/**
 * DOMXPath envelope.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class SchematronXPath extends DOMXPath
{
    /**
     * ($registerNodeNS is FALSE in opposition to DOMXPath default value)
     */
    public function query($expression, DOMNode $context = null, $registerNodeNS = false)
    {
        return parent::query($expression, $context, $registerNodeNS);
    }


    /**
     * ($registerNodeNS is FALSE in opposition to DOMXPath default value)
     */
    public function evaluate($expression, DOMNode $context = null, $registerNodeNS = false)
    {
        return parent::evaluate($expression, $context, $registerNodeNS);
    }


    public function queryContext($expression, DOMNode $context = null, $registerNodeNS = false)
    {
        if (isset($expression[0]) && $expression[0] !== '.' && $expression[0] !== '/') {
            $expression = "//$expression";
        }
        return $this->query($expression, $context, $registerNodeNS);
    }
}


/**
 * Thrown when schematron schema source is malformed (not well-formed).
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class SchematronException extends \RuntimeException
{
}
