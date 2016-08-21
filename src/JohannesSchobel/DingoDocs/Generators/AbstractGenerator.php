<?php

namespace JohannesSchobel\DingoDocs\Generators;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Faker\Factory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;
use JohannesSchobel\DingoDocs\Parsers\RuleDescriptionParser;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;

abstract class AbstractGenerator
{
    private $reader = null;

    public function __construct()
    {
        $this->reader = new SimpleAnnotationReader();
        $this->reader->addNamespace('JohannesSchobel\\DingoDocs\\Models\\Annotations');

        AnnotationRegistry::registerLoader(function ($classfile) {
            $path = str_replace(['JohannesSchobel\\DingoDocs\\Generators', '\\'], ['', DIRECTORY_SEPARATOR], __DIR__) . $classfile . ".php";

            if (file_exists($path)) {
                require_once $path;
                return true;
            }
        });
    }

    public function getReader() {
        return $this->reader;
    }

    /**
     * @param $route
     *
     * @return mixed
     */
    //abstract protected function getUri($route);

    /**
     * @param  \Illuminate\Routing\Route $route
     *
     * @return \JohannesSchobel\DingoDocs\Models\Route
     */
    //abstract public function processRoute($route);

    /**
     * @param array $routeData
     * @param array $routeAction
     * @param array $bindings
     *
     * @return mixed
     */
    protected function getParameters($routeData, $routeAction, $bindings)
    {
        $validator = Validator::make([], $this->getRouteRules($routeAction['uses'], $bindings));
        foreach ($validator->getRules() as $attribute => $rules) {
            $attributeData = [
                'required' => false,
                'type' => 'string',
                'default' => '',
                'value' => '',
                'description' => [],
            ];
            foreach ($rules as $rule) {
                $this->parseRule($rule, $attributeData, $routeData['id']);
            }
            $routeData['parameters'][$attribute] = $attributeData;
        }

        return $routeData;
    }

    /**
     * @param  $route
     *
     * @return \Illuminate\Http\Response
     */
    protected function getRouteResponse($route, $bindings)
    {
        $uri = $this->addRouteModelBindings($route, $bindings);

        $methods = $route->getMethods();

        return $this->callRoute(array_shift($methods), $uri);
    }

    /**
     * @param $route
     * @param array $bindings
     *
     * @return mixed
     */
    protected function addRouteModelBindings($route, $bindings)
    {
        $uri = $this->getUri($route);
        foreach ($bindings as $model => $id) {
            $uri = str_replace('{'.$model.'}', $id, $uri);
        }

        return $uri;
    }

    /**
     * Get the description of a route
     *
     * TITLE = short description, DESCRIPTION = long description
     *
     * @param  String $route
     * @return array
     */
    protected function getRouteDescriptionAnnotation($route)
    {
        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);
        $reflectionMethod = $reflection->getMethod($method);

        $comment = $reflectionMethod->getDocComment();
        $phpdoc = new DocBlock($comment);

        $title = $phpdoc->getShortDescription();
        $description = $phpdoc->getLongDescription()->getContents();

        if(empty($title)) {
            dingodocs_msg('E', 'Route ' . $route . ' : Short Description is missing!');
        }

        if(empty($description)) {
            dingodocs_msg('I', 'Route ' . $route . ' : Long Description is missing.');
        }

        return [
            'title' => $title,
            'description' => $description,
        ];
    }

    /**
     * Returns the Group of the route
     *
     * Checks for the annotation on method, then on class
     * @param  string  $route
     * @return string
     */
    protected function getRouteGroupAnnotation($route)
    {
        $defaultgroup = config('dingodocs.defaultgroupname');

        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);

        // check, if there is a method-docblock
        $comment = $reflection->getMethod($method)->getDocComment();
        if ($comment) {
            $phpdoc = new DocBlock($comment);
            foreach ($phpdoc->getTags() as $tag) {
                if ($tag->getName() === config('dingodocs.tags.group')) {
                    return $tag->getContent();
                }
            }
        }

        // we haven't found a method docblock - now check for the class
        $comment = $reflection->getDocComment();
        if ($comment) {
            $phpdoc = new DocBlock($comment);
            foreach ($phpdoc->getTags() as $tag) {
                if ($tag->getName() === config('dingodocs.tags.group')) {
                    return $tag->getContent();
                }
            }
        }

        dingodocs_msg('W', 'Route ' . $route . ' : No @' . config('dingodocs.tags.group') . ' annotation found. Using default group!');
        return $defaultgroup;
    }

    /**
     * Returns the transient annotation
     *
     * TRUE means, the route shall not be displayed
     *
     * @param $route
     * @return bool
     */
    public function getRouteTransientAnnotation($route)
    {
        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);
        $reflectionMethod = $reflection->getMethod($method);

        $comment = $reflectionMethod->getDocComment();
        if($comment) {
            $phpdoc = new DocBlock($comment);
            foreach($phpdoc->getTags() as $tag) {
                if($tag->getName() === config('dingodocs.tags.transient')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the status code annotations
     *
     * CODE and DESCRIPTION values
     *
     * @param $route
     * @return array
     */
    protected function getRouteStatusCodeAnnotation($route)
    {
        $result = [];

        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);
        $reflectionMethod = $reflection->getMethod($method);

        $comment = $reflectionMethod->getDocComment();
        if($comment) {
            $phpdoc = new DocBlock($comment);
            foreach($phpdoc->getTags() as $tag) {
                if($tag->getName() === config('dingodocs.tags.status')) {
                    $status = new \stdClass();

                    $data = $this->extractTagValues($tag->getDescription());
                    $status->code = $data[0];

                    // remove the first element
                    array_shift($data);
                    $status->description = implode(" ", $data);

                    $result[] = $status;
                }
            }
        }

        if(empty($result)) {
            dingodocs_msg('W', 'Route ' . $route . ' : No @' . config('dingodocs.tags.status') . ' annotation found.');
        }

        return $result;
    }

    /**
     * @param $route
     * @return TransformerAbstract | null
     */
    protected function getRouteTransformerAnnotation($route) {
        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);
        $reflectionMethod = $reflection->getMethod($method);

        $comment = $reflectionMethod->getDocComment();
        if ($comment) {
            $phpdoc = new DocBlock($comment);
            foreach ($phpdoc->getTags() as $tag) {
                if ($tag->getName() === config('dingodocs.tags.transformer')) {
                    $class = $tag->getContent();
                    if (is_subclass_of($class, TransformerAbstract::class)) {
                        $r = new $class();
                        return $r;
                    }
                    else {
                        dingodocs_msg('E', 'Route ' . $route . ' : Could not find Transformer Class ' . $class);
                    }
                }
            }
        }

        dingodocs_msg('W', 'Route ' . $route . ' : No @' . config('dingodocs.tags.transformer') .  ' annotation found.');
        return null;
    }

    /**
     * Returns, if the route needs authentication
     *
     * TRUE means, that the user needs to be authenticated to call this route
     *
     * @param $route
     * @return bool
     */
    protected function getRouteAuthenticatedAnnotation($route) {
        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);

        // check, if there is a method-docblock
        $comment = $reflection->getMethod($method)->getDocComment();
        if ($comment) {
            $phpdoc = new DocBlock($comment);
            foreach ($phpdoc->getTags() as $tag) {
                if ($tag->getName() === config('dingodocs.tags.authenticated')) {
                    return true;
                }
            }
        }

        // we haven't found a method docblock - now check for the class
        $comment = $reflection->getDocComment();
        if ($comment) {
            $phpdoc = new DocBlock($comment);
            foreach ($phpdoc->getTags() as $tag) {
                if ($tag->getName() === config('dingodocs.tags.authenticated')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  $route
     * @param  array $bindings
     *
     * @return array
     */
    protected function getRouteRules($route, $bindings)
    {
        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);
        $reflectionMethod = $reflection->getMethod($method);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterType = $parameter->getClass();
            if (! is_null($parameterType) && class_exists($parameterType->name)) {
                $className = $parameterType->name;

                if (is_subclass_of($className, FormRequest::class)) {
                    $parameterReflection = new $className;
                    // Add route parameter bindings
                    $parameterReflection->query->add($bindings);
                    $parameterReflection->request->add($bindings);

                    if (method_exists($parameterReflection, 'validator')) {
                        return $parameterReflection->validator()->getRules();
                    } else {
                        return $parameterReflection->rules();
                    }
                }
            }
        }

        return [];
    }

    /**
     * Gets the Validator Class for a given route
     *
     * @param  $route
     * @return FormRequest | null
     */
    protected function getRouteValidator($route) {
        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);
        $reflectionMethod = $reflection->getMethod($method);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterType = $parameter->getClass();
            if (! is_null($parameterType) && class_exists($parameterType->name)) {
                $className = $parameterType->name;

                if (is_subclass_of($className, FormRequest::class)) {
                    $validator = new $className;

                    // Add route parameter bindings
                    return $validator;
                }
            }
        }

        dingodocs_msg('I', 'Route ' . $route . ' : No Validator (FormRequest) found.');
        return null;
    }

    /**
     * Returns the Validation Parameters for a given Validator
     *
     * @param \JohannesSchobel\DingoDocs\Models\Route $route the "unfinished" route
     * @param FormRequest | null $formRequest
     * @return array
     */
    protected function getRouteValidationParameters($route, $formRequest) {
        $parameters = [];

        if(is_null($formRequest) || ! is_subclass_of($formRequest, FormRequest::class)) {
            return $parameters;
        }

        if (method_exists($formRequest, 'validator')) {
            $parameters = $formRequest->validator()->getRules();
        } else {
            $parameters = $formRequest->rules();
        }

        // make a validator with the gathered parameters
        $validator = Validator::make([], $parameters);

        foreach($validator->getRules() as $attribute => $rules) {

            $dummy = [
                'required' => false,
                'type' => 'string',
                'value' => '',
                'default' => '',
                'details' => []
            ];

            foreach($rules as $rule) {
                $this->parseValidationRule($rule, $dummy, $route->id);
            }

            $parameters[$attribute] = $dummy;
        }

        return $parameters;
    }

    protected function getRouteRequestAnnotation($route) {
        $result = "";

        list($class, $method) = explode('@', $route);
        $reflection = new ReflectionClass($class);

        // check, if there is a method-docblock
        $comment = $reflection->getMethod($method)->getDocComment();
        if ($comment) {
            $phpdoc = new DocBlock($comment);
            foreach ($phpdoc->getTags() as $tag) {
                if ($tag->getName() === config('dingodocs.tags.request')) {
                    return $tag->getContent();
                }
            }
        }

        dingodocs_msg('W', 'Route ' . $route . ' : No @' . config('dingodocs.tags.request') .  ' annotation found.');
        return $result;
    }

    /**
     * @param  array  $arr
     * @param  string  $first
     * @param  string  $last
     *
     * @return string
     */
    protected function fancyImplode($arr, $first, $last)
    {
        $arr = array_map(function ($value) {
            return '`'.$value.'`';
        }, $arr);
        array_push($arr, implode($last, array_splice($arr, -2)));

        return implode($first, $arr);
    }

    /**
     * Parses the Rule
     *
     * @param  string  $rule
     * @param  array  $attributeData
     * @param  string $seed
     *
     * @return void
     */
    protected function parseValidationRule($rule, &$attributeData, $seed)
    {
        $faker = Factory::create();
        $faker->seed(crc32($seed));

        $parsedRule = $this->parseStringRule($rule);
        $parsedRule[0] = $this->normalizeRule($parsedRule[0]);
        list($rule, $parameters) = $parsedRule;

        switch ($rule) {
            case 'required':
                $attributeData['required'] = true;
                break;
            case 'accepted':
                $attributeData['required'] = true;
                $attributeData['type'] = 'boolean';
                $attributeData['value'] = true;
                break;
            case 'after':
                $attributeData['type'] = 'date';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with(date(DATE_RFC850, strtotime($parameters[0])))->getDescription();
                $attributeData['value'] = date(DATE_RFC850, strtotime('+1 day', strtotime($parameters[0])));
                break;
            case 'alpha':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->getDescription();
                $attributeData['value'] = $faker->word;
                break;
            case 'alpha_dash':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->getDescription();
                break;
            case 'alpha_num':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->getDescription();
                break;
            case 'in':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                $attributeData['value'] = $faker->randomElement($parameters);
                break;
            case 'not_in':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                $attributeData['value'] = $faker->word;
                break;
            case 'min':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'max':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'between':
                $attributeData['type'] = 'numeric';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                $attributeData['value'] = $faker->numberBetween($parameters[0], $parameters[1]);
                break;
            case 'before':
                $attributeData['type'] = 'date';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with(date(DATE_RFC850, strtotime($parameters[0])))->getDescription();
                $attributeData['value'] = date(DATE_RFC850, strtotime('-1 day', strtotime($parameters[0])));
                break;
            case 'date_format':
                $attributeData['type'] = 'date';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'different':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'digits':
                $attributeData['type'] = 'numeric';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                $attributeData['value'] = $faker->randomNumber($parameters[0], true);
                break;
            case 'digits_between':
                $attributeData['type'] = 'numeric';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'image':
                $attributeData['type'] = 'image';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->getDescription();
                break;
            case 'json':
                $attributeData['type'] = 'string';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->getDescription();
                $attributeData['value'] = json_encode(['foo', 'bar', 'baz']);
                break;
            case 'mimetypes':
            case 'mimes':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                break;
            case 'required_if':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'required_unless':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'required_with':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                break;
            case 'required_with_all':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($this->fancyImplode($parameters, ', ', ' and '))->getDescription();
                break;
            case 'required_without':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                break;
            case 'required_without_all':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($this->fancyImplode($parameters, ', ', ' and '))->getDescription();
                break;
            case 'same':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'size':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'timezone':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->getDescription();
                $attributeData['value'] = $faker->timezone;
                break;
            case 'exists':
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with([Str::singular($parameters[0]), $parameters[1]])->getDescription();
                break;
            case 'active_url':
                $attributeData['type'] = 'url';
                $attributeData['value'] = $faker->url;
                break;
            case 'regex':
                $attributeData['type'] = 'string';
                $attributeData['details'][] = RuleDescriptionParser::parse($rule)->with($parameters)->getDescription();
                break;
            case 'boolean':
                $attributeData['value'] = true;
                $attributeData['type'] = $rule;
                break;
            case 'array':
                $attributeData['value'] = $faker->word;
                $attributeData['type'] = $rule;
                break;
            case 'date':
                $attributeData['value'] = $faker->date();
                $attributeData['type'] = $rule;
                break;
            case 'email':
                $attributeData['value'] = $faker->safeEmail;
                $attributeData['type'] = $rule;
                break;
            case 'string':
                $attributeData['value'] = $faker->word;
                $attributeData['type'] = $rule;
                break;
            case 'integer':
                $attributeData['value'] = $faker->randomNumber();
                $attributeData['type'] = $rule;
                break;
            case 'numeric':
                $attributeData['value'] = $faker->randomNumber();
                $attributeData['type'] = $rule;
                break;
            case 'url':
                $attributeData['value'] = $faker->url;
                $attributeData['type'] = $rule;
                break;
            case 'ip':
                $attributeData['value'] = $faker->ipv4;
                $attributeData['type'] = $rule;
                break;
        }

        if ($attributeData['value'] === '') {
            $attributeData['value'] = $faker->word;
        }
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $parameters
     * @param  array  $cookies
     * @param  array  $files
     * @param  array  $server
     * @param  string  $content
     *
     * @return \Illuminate\Http\Response
     */
    //abstract public function callRoute($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null);

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param  array  $headers
     *
     * @return array
     */
    protected function transformHeadersToServerVars(array $headers)
    {
        $server = [];
        $prefix = 'HTTP_';

        foreach ($headers as $name => $value) {
            $name = strtr(strtoupper($name), '-', '_');

            if (! Str::startsWith($name, $prefix) && $name !== 'CONTENT_TYPE') {
                $name = $prefix.$name;
            }

            $server[$name] = $value;
        }

        return $server;
    }

    /**
     * Parse a string based rule.
     *
     * @param  string  $rules
     *
     * @return array
     */
    protected function parseStringRule($rules)
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "max:200" states that the value may only be 200 characters long.
        if (strpos($rules, ':') !== false) {
            list($rules, $parameter) = explode(':', $rules, 2);

            $parameters = $this->parseParameters($rules, $parameter);
        }

        return [strtolower(trim($rules)), $parameters];
    }

    /**
     * Parse a parameter list.
     *
     * @param  string  $rule
     * @param  string  $parameter
     *
     * @return array
     */
    protected function parseParameters($rule, $parameter)
    {
        if (strtolower($rule) === 'regex') {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }

    /**
     * Normalizes a rule so that we can accept short types.
     *
     * @param  string $rule
     *
     * @return string
     */
    protected function normalizeRule($rule)
    {
        switch ($rule) {
            case 'int':
                return 'integer';
            case 'bool':
                return 'boolean';
            default:
                return $rule;
        }
    }

    private function extractTagValues($line) {
        $result = explode(" ", $line);
        $result = array_filter($result, function($value) { return $value !== ''; });
        return $result;
    }
}
