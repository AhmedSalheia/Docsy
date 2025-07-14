<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\Enums\HTTPMethod;
use Ahmedsalheia\Docsy\Enums\ParamLocation;
use Ahmedsalheia\Docsy\traits\ArrayJsonSerialization;
use Ahmedsalheia\Docsy\traits\HasParent;
use GuzzleHttp\Exception\GuzzleException;
use function DeepCopy\deep_copy;

class DocsyRequest implements \JsonSerializable
{
    use ArrayJsonSerialization;
    use HasParent;
    public HTTPMethod $method;
    public string $uri;
    public string $name = '';
    public string $description = '';
    /** @var DocsyParam[] */
    public array $pathParams = [];
    /** @var DocsyParam[] */
    public array $queryParams = [];
    /** @var DocsyParam[] */
    public array $bodyParams = [];
    /** @var DocsyParam[] */
    public array $headerParams = [];
    public bool $requires_auth = false;
    public ?string $auth_scheme = 'Bearer';
    public ?string $auth_token_placeholder = '{{token}}';
    /** @var DocsyExample[] */
    public array $examples = [];

    public ?DocsyCollection $collection = null;

    /**
     * @param string $method
     * @param string $uri
     * @param string $name
     * @param string $description
     * @param array[] $headerParams
     * @param array[] $bodyParams
     * @param array[] $queryParams
     * @param bool $requires_auth
     * @param array $pathParams
     */
    public function __construct(string $method, string $uri, string $name = '', string $description = '', array $headerParams = [], array $bodyParams = [], array $queryParams = [], bool $requires_auth = false, array $pathParams = []) {
        // adding path params
        foreach ($pathParams as $pathParam) {
            $this->addPathParam($pathParam);
        }

        preg_match_all('/\{[\w\W]+(\?)*}/', $uri, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $urlParam = str_replace(['{', '}'], '', $match);
                $required = !str_ends_with($match, '?');

                $this->addPathParam(trim($urlParam,'?'), required: $required);
            }
        }

        // add headers
        foreach ($headerParams as $headerParam) {
            $this->addHeaderParam($headerParam);
        }

        // adding Body Params
        foreach ($pathParams as $pathParam) {
            $this->addBodyParam($pathParam);
        }

        // adding query Params
        foreach ($queryParams as $queryParam) {
            $this->addQueryParam($queryParam);
        }
        preg_match_all('/\?\w[\w\W]+/', $uri, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $params = explode('&',trim($match, '?'));
                foreach ($params as $param) {
                    $param = explode('=', $param);
                    $this->addQueryParam(trim($param[0]), example: $param[1]);
                }
            }
        }

        if (!HTTPMethod::isValid($method)) throw new \InvalidArgumentException("Invalid HTTP method [$method]");
        $this->method = HTTPMethod::get($method);
        $this->requires_auth = $requires_auth;

        // cleaning up the url and making the url params values
        $this->uri = explode('?', str_replace(['?}','{','}'], ['}','{{','}}'], $uri))[0];

        // add name and description
        $this->name = $name ?: $this->uri;
        $this->description = $description;
    }

    public function getParams(ParamLocation $paramLocation): array
    {

        return $this->{$paramLocation->value . 'Params'};
    }
    public function hasParam(ParamLocation $paramLocation, string $name): bool
    {
        return array_key_exists($name, $this->getParams($paramLocation));
    }
    public function addPathParam(string|array $name, string $description = "", string $type = "", bool $required = false, mixed $example = ''): static
    {
        if (is_array($name)) {
            $name['in'] = ParamLocation::Path->value;
            $this->pathParams[$name['name']] = DocsyParam::fromArray($name);

        } else
            $this->pathParams[$name] = new DocsyParam($name, ParamLocation::Path, $description, $type, $required, $example, $this);

        return $this;
    }
    public function addHeaderParam(string|array $name, string $description = "", string $type = "", bool $required = false, mixed $example = ''): static
    {
        if (is_array($name)) {
            $name['in'] = ParamLocation::Header->value;
            $this->headerParams[$name['name']] = DocsyParam::fromArray($name);

        } else
            $this->headerParams[$name] = new DocsyParam($name, ParamLocation::Header, $description, $type, $required, $example, $this);

        return $this;
    }
    public function addQueryParam(string|array $name, string $description = "", string $type = "", bool $required = false, mixed $example = ''): static
    {
        if (is_array($name)) {
            $name['in'] = ParamLocation::Query->value;
            $this->queryParams[$name['name']] = DocsyParam::fromArray($name);

        } else
            $this->queryParams[$name] = new DocsyParam($name, ParamLocation::Query, $description, $type, $required, $example, $this);

        return $this;
    }
    public function addBodyParam(string|array $name, string $description = "", string $type = "", bool $required = false, mixed $example = '') : static
    {
        if (is_array($name)) {
            $name['in'] = ParamLocation::Body->value;
            $this->bodyParams[$name['name']] = DocsyParam::fromArray($name);

        } else
            $this->bodyParams[$name] = new DocsyParam($name, ParamLocation::Body, $description, $type, $required, $example, $this);

        return $this;
    }
    public function removeParam(ParamLocation $paramLocation, string $name) : static
    {
        if ($this->hasParam($paramLocation, $name))
            unset($this->getParams($paramLocation)[$name]);

        return $this;
    }

    public function getCollection(): ?DocsyCollection
    {
        if ($this->collection !== null)
            return $this->collection;

        $parent = $this->getParent();

        while ($parent !== null) {
            if ($parent instanceof DocsyCollection) {
                $this->collection = $parent;
                $this->setGlobals();
                return $this->collection;
            }
            $parent = $parent->getParent();
        }

        return null;
    }
    public function getBaseUrl(): ?string
    {
        return $this->getCollection()?->baseUrl;
    }

    public function addExample(DocsyExample $example): static
    {
        $this->examples[$example->name] = $example;
        return $this;
    }
    public function removeExample(string $name): static
    {
        if (array_key_exists($name, $this->examples))
            unset($this->examples[$name]);
        return $this;
    }
    public function getExamples(string $label = ''): DocsyExample | array
    {
        return $label ? $this->examples[$label] : $this->examples;
    }

    /**
     * @throws GuzzleException
     */
    public function snapResponse(string $label, bool $force = false, string $description = '', array $data = null): static
    {
        $this->setGlobals();
        $request = deep_copy($this);
        
        if (!empty($data['url']))
            foreach ($data['url'] as $param => $value) {
                $request->urlParams[$param]->setExample($value);
            }
        if (!empty($data['query']))
            foreach ($data['query'] as $param => $value) {
                $request->queryParams[$param]->setExample($value);
            }
        if (!empty($data['body']))
            foreach ($data['body'] as $param => $value) {
                $request->body[$param]->setExample($value);
            }
        if (!empty($data['headers']))
            foreach ($data['headers'] as $header => $value) {
                $this->headerParams[$header]->setExample($value);
            }

        $example = (new DocsyExample($request, $label, $description))->setParent($this)->runExample($force);
        $this->addExample($example);

        return $this;
    }
    public function setGlobals(): static
    {
        if ($this->getCollection())
        {
            $globals = $this->getCollection()?->globals();

            $this->headerParams = array_merge($this->headerParams, $globals['headers'] ?? []);
            $this->bodyParams = array_merge($this->bodyParams, $globals['body'] ?? []);
            $this->queryParams = array_merge($this->queryParams, $globals['query'] ?? []);
        }
        return $this;
    }

    public function toArray(): array
    {
        $this->setGlobals();

        // Helper to serialize arrays of DocsyParam or scalar values
        $serializeParams = fn(array $params): array => array_map(
            fn($param) => $param instanceof \JsonSerializable ? $param->jsonSerialize() : $param,
            $params
        );

        return [
            'class_name' => 'DocsyRequest',
            'method' => $this->method,
            'uri' => $this->uri,
            'name' => $this->name,
            'description' => $this->description,
            'pathParams' => $serializeParams($this->pathParams),
            'queryParams' => $serializeParams($this->queryParams),
            'bodyParams' => $serializeParams($this->bodyParams),
            'headerParams' => $this->headerParams,
            'requires_auth' => $this->requires_auth,
            'examples' => array_map(fn($e) => $e->toArray(), $this->examples),
        ];
    }
    public static function fromArray(array $array, $parent = null): static
    {
        return (new self($array['method'],
            $array['uri'],
            $array['name']??'',
            $array['description']??'',
            $array['queryParams']??[],
            $array['bodyParams']??[],
            $array['headerParams']??[],
            $array['requires_auth']??false,
            $array['pathParams']??[])
        )->setParent($parent)->setGlobals();
    }
}
