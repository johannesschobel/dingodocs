<?php

namespace JohannesSchobel\DingoDocs\Models;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Faker\Factory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JohannesSchobel\DingoDocs\Models\Annotations\Authentication;
use JohannesSchobel\DingoDocs\Models\Annotations\Transformer;
use JohannesSchobel\DingoDocs\Models\Annotations\Transient;
use JohannesSchobel\DingoDocs\Parsers\RuleDescriptionParser;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use Dingo\Api\Routing\Route;

/**
 * Class Endpoint
 * @package JohannesSchobel\DingoDocs\Models
 */
class Endpoint
{
    /**
     * @var SimpleAnnotationReader
     */
    protected $reader;

    /**
     * @var Collection
     */
    protected $classAnnotations;

    /**
     * @var Collection
     */
    protected $methodAnnotations;

    /**
     * @var Route
     */
    protected $route;

    /**
     * Endpoint constructor.
     * @param SimpleAnnotationReader $reader
     * @param \Dingo\Api\Routing\Route $route
     * @internal param string $group
     * @internal param string $uri
     */
    public function __construct(SimpleAnnotationReader $reader, Route $route)
    {
        $this->reader = $reader;
        $this->route = $route;

        $this->classAnnotations = $this->getClassAnnotations();
        $this->methodAnnotations = $this->getMethodAnnotations();
    }

    /**
     * Get the short description of the route
     *
     * @return string
     */
    public function getShortDescription()
    {
        $name = (new DocBlock($this->getMethodReflector()))->getShortDescription();
        if(empty($name)) {
            dingodocs_msg('E', $this->route, 'does not provide a short description!');
            $name = $this->getURI();
        }
        return $name;
    }

    /**
     * Get the long (detailed) description of the route
     *
     * @return string
     */
    public function getLongDescription()
    {
        $description = (new DocBlock($this->getMethodReflector()))->getLongDescription()->getContents();
        if(empty($description)) {
            dingodocs_msg('W', $this->route, 'does not provide a long description!');
        }
        return $description;
    }

    /**
     * Get the URI of the route
     *
     * @return string
     */
    public function getURI()
    {
        return $this->route->uri();
    }

    /**
     * Get the ID of the route
     *
     * @return string
     */
    public function getID()
    {
        return md5($this->route->uri() . ":" . implode($this->route->getMethods()));
    }

    /**
     * Get the group of the route
     *
     * @return string
     */
    public function getGroup()
    {
        $annotation = $this->findAnnotationByType('Group');
        if(!empty($annotation)) {
            return $annotation->getValue();
        }

        dingodocs_msg('W', $this->route, 'has no @Group annotation!');
        return config('dingodocs.defaults.group');
    }

    /**
     * Check if the route needs authentication
     *
     * @return Authentication | null Annotation
     */
    public function getAuthentication()
    {
        $annotation = $this->findAnnotationByType('Authentication');

        if(!empty($annotation)) {
            return $annotation;
        }

        dingodocs_msg('I', $this->route, 'has no @Authentication annotation!');
        return null;
    }

    /**
     * Returns all methods assigned for this route
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->route->getMethods();
    }

    /**
     * Check if the route is transient (must not be displayed)
     *
     * @return Transient | null
     */
    public function getTransient()
    {
        $annotation = $this->findAnnotationByType('Transient');
        if(!empty($annotation)) {
            return $annotation;
        }

        dingodocs_msg('I', $this->route, 'has no @Transient annotation!');
        return null;
    }

    /**
     * Get the assigned Transformer for the route
     *
     * @return Transformer | null
     */
    public function getTransformer()
    {
        $annotation = $this->findMethodAnnotationByType('Transformer');

        if(!empty($annotation)) {
            return $annotation;
        }

        dingodocs_msg('I', $this->route, 'has no @Transformer annotation!');
        return null;
    }

    /**
     * Get the assigned FormRequest Validator for the route
     *
     * @return FormRequest | mixed
     */
    public function getValidator()
    {
        $reflectionMethod = $this->getMethodReflector();
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterType = $parameter->getClass();
            if (! is_null($parameterType) && class_exists($parameterType->name)) {

                $validatorClass = $parameterType->name;

                if (is_subclass_of($validatorClass, FormRequest::class)) {
                    $validator = new $validatorClass;

                    // Add route parameter bindings
                    return $validator;
                }
            }
        }

        // the annotation was not found - set the default value
        dingodocs_msg('I', $this->route, 'does not provide a Validator (FormRequest)!');
        return config('dingodocs.defaults.transformer');
    }

    public function getQueryParameters()
    {
        $annotation = $this->findMethodAnnotationByType('QueryParameters');
        if(isset($annotation)) {
            return $annotation;
        }

        dingodocs_msg('I', $this->route, 'does not provide QueryParameters!');
        return null;
    }

    public function getValidatorParameters() {
        $formRequest = $this->getValidator();

        if(is_null($formRequest) || ! is_subclass_of($formRequest, FormRequest::class)) {
            dingodocs_msg('I', $this->route, 'does not provide ValidatorParameters!');
            return config('dingodocs.defaults.validatorparameters');
        }

        if (method_exists($formRequest, 'validator')) {
            $parameters = $formRequest->validator()->getRules();
        } else {
            $parameters = $formRequest->rules();
        }

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
                $this->parseValidationRule($rule, $dummy, $this->getID());
            }

            $parameters[$attribute] = $dummy;
        }

        return $parameters;
    }

    public function getRequest() {
        $annotation = $this->findAnnotationByType('Request');
        if(isset($annotation)) {
            return $annotation;
        }

        dingodocs_msg('I', $this->route, 'does not provide an @Request annotation!');
        return null;
    }

    public function getResponse() {
        $annotation = $this->findAnnotationByType('Response');
        if(isset($annotation)) {
            return $annotation;
        }

        dingodocs_msg('I', $this->route, 'does not provide an @Response annotation!');
        return null;
    }

    /**
     * Get an annotation from the method by type.
     *
     * @param string $type
     * @return mixed
     */
    private function findMethodAnnotationByType($type)
    {
        return array_first($this->methodAnnotations, function ($key, $annotation) use ($type) {
            $type = sprintf('JohannesSchobel\\DingoDocs\\Models\\Annotations\\%s', $type);
            return $annotation instanceof $type;
        });
    }

    /**
     * Get an annotation from the class by type.
     *
     * @param string $type
     * @return mixed
     */
    private function findClassAnnotationByType($type)
    {
        return array_first($this->classAnnotations, function ($key, $annotation) use ($type) {
            $type = sprintf('JohannesSchobel\\DingoDocs\\Models\\Annotations\\%s', $type);
            return $annotation instanceof $type;
        });
    }

    /**
     * Get the annotation from the method, then from class for type
     *
     * @param $type
     * @return mixed | null
     */
    private function findAnnotationByType($type)
    {
        $annotation = $this->findMethodAnnotationByType($type);
        if(!empty($annotation)) {
            return $annotation;
        }

        $annotation = $this->findClassAnnotationByType($type);
        if(!empty($annotation)) {
            return $annotation;
        }

        return null;
    }

    /**
     * @return ReflectionClass
     */
    private function getClassReflector()
    {
        list($class, $method) = explode('@', $this->route->getAction()['uses']);
        $result = new ReflectionClass($class);
        return $result;
    }

    /**
     * @return \ReflectionMethod
     */
    private function getMethodReflector()
    {
        list($class, $method) = explode('@', $this->route->getAction()['uses']);
        $tmp = new ReflectionClass($class);
        $result = $tmp->getMethod($method);
        return $result;
    }

    /**
     * @return Collection
     */
    private function getClassAnnotations()
    {
        $class = $this->getClassReflector();
        $annotations = $this->reader->getClassAnnotations($class);
        return collect($annotations);
    }

    /**
     * @return Collection
     */
    private function getMethodAnnotations()
    {
        $method = $this->getMethodReflector();
        $annotations = $this->reader->getMethodAnnotations($method);
        return collect($annotations);
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
}

