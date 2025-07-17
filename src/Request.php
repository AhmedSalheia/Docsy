<?php

namespace Docsy;

use Docsy\Enums\HTTPMethod;
use Docsy\Enums\ParamLocation;
use Docsy\Support\Example;
use Docsy\Support\Param;
use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\HasExamples;
use Docsy\Traits\HasID;
use Docsy\Traits\HasParams;
use Docsy\Traits\HasParent;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JsonSerializable;

class Request implements JsonSerializable
{
    use ArrayJsonSerialization, HasParent, HasID, HasParams, HasExamples;

    public HTTPMethod $method;

    public string $scheme;
    public string $uri;
    public array $path;

    public string $name = '';
    public string $description = '';

    public bool $requires_auth = false;
    public ?string $auth_scheme = 'Bearer';
    public ?string $auth_token_placeholder = '{{token}}';

    public ?Collection $collection = null;

    /**
     * @param HTTPMethod|string $method
     * @param string $uri
     * @param string $name
     * @param string $description
     * @param Param[] $headerParams
     * @param Param[] $bodyParams
     * @param Param[] $queryParams
     * @param bool $requires_auth
     */
    public function __construct(HTTPMethod | string $method, string $uri, string $name = '', string $description = '', array $headerParams = [], array $bodyParams = [], array $queryParams = [], bool $requires_auth = false) {
        $this->setID();

        if (!HTTPMethod::isValid($method)) throw new InvalidArgumentException("Invalid HTTP method [$method]");
        $this->method = HTTPMethod::get($method);

        $this->requires_auth = $requires_auth;

        $urlData = preg_split('/:\/\//', $uri, -1, PREG_SPLIT_NO_EMPTY);
        $this->scheme = $urlData[0] ?? null;

        $urlData = preg_split('/\?([^}:])/', $urlData[1] ?? $uri, -1, PREG_SPLIT_NO_EMPTY);
        $this->uri = $urlData[0];

        $this->path = $this->getPath($this->uri);

        // add name and description
        $this->name = $name ?: $this->uri;
        $this->description = $description;

        // add headers
        foreach ($headerParams as $headerParam) {
            $this->addHeaderParam($headerParam);
        }

        // adding Body Params
        foreach ($bodyParams as $bodyParam) {
            $this->addBodyParam($bodyParam);
        }

        // adding query Params
        foreach ($queryParams as $queryParam) {
            $this->addQueryParam($queryParam);
        }

        $this->checkQueryParamsInURI($uri);
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
    public function getCollection(): ?Collection
    {
        if ($this->collection !== null)
            return $this->collection;

        $parent = $this->getParent();

        while ($parent !== null) {
            if ($parent instanceof Collection) {
                $this->collection = $parent;
                $this->setGlobals();
                $this->scheme = preg_split('/:\/\//', $parent->baseUrl, -1, PREG_SPLIT_NO_EMPTY)[0];
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
    private function getPath($uri): array
    {
        // clear scheme if exists:
        $uriWithoutScheme = str_replace(['http://', 'https://'],'', $uri);
        $pathData = explode('/', $uriWithoutScheme);

        // adding path params
        foreach ($pathData as $index => $path) {
            if (str_starts_with($path,'{')) // Path Param
            {
                $pathParamData = explode(':',str_replace(['{', '}'], '', $path));
                $pathParamName = $pathParamData[0];
                $pathParamValue = $pathParamData[1] ?? '';
                $required = !str_ends_with($pathParamName, '?');

                $path = $this->addAndReturnParam(ParamLocation::Path, trim($pathParamName,'?'), required: $required, value: $pathParamValue);
                $this->addPathParam($path);
                $pathData[$index] = $path;
            }
        }

        return $pathData;
    }
    private function checkQueryParamsInURI($uri): void
    {
        preg_match_all('/\?\w[\w\W]+/', $uri, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $params = explode('&',trim($match, '?'));
                foreach ($params as $param) {
                    $param = explode('=', $param);
                    $this->addQueryParam(trim($param[0]), value: $param[1]);
                }
            }
        }
    }
    private function stripExampleRequest() : static
    {
        return (new Request(
            $this->method,
            $this->uri,
            $this->name,
            $this->description,
            $this->headerParams,
            $this->bodyParams,
            $this->queryParams,
            $this->requires_auth
        ))
            ->setID($this->id);
    }

    /**
     * @throws GuzzleException
     */
    public function snapExample(string $label, bool $force = false, string $description = '', array $data = null): static
    {
        $this->setGlobals();
        $request = $this->stripExampleRequest();

        if (!empty($data['path']))
            foreach ($data['path'] as $param => $value) {
                $request->getParamByName(ParamLocation::Path, $param)->setValue($value);
            }
        if (!empty($data['query']))
            foreach ($data['query'] as $param => $value) {
                $request->getParamByName(ParamLocation::Query, $param)->setValue($value);
            }
        if (!empty($data['body']))
            foreach ($data['body'] as $param => $value) {
                $request->getParamByName(ParamLocation::Body, $param)->setValue($value);
            }
        if (!empty($data['headers']))
            foreach ($data['headers'] as $param => $value) {
                $request->getParamByName(ParamLocation::Header, $param)->setValue($value);
            }

        $example = (new Example($request, $label, $description))->setParent($this)->run($force);
        $this->addExample($example);

        return $this;
    }

    /**
     * @throws GuzzleException
     */
    public function run(): Example
    {
        $this->setGlobals();
        $request = $this->stripExampleRequest();

        return (new Example($request,"Default Runner"))->setParent($this)->run(noCache: true);
    }

    public function toArray(): array
    {
        $this->setGlobals();

        // Helper to serialize arrays of Param or scalar values
        $serializeParams = fn(array $params): array => array_map(
            fn($param) => $param instanceof JsonSerializable ? $param->jsonSerialize() : $param,
            $params
        );

        return [
            'id' => $this->id,
            'class_name' => basename(get_class($this)),
            'method' => $this->method,
            'scheme' => $this->scheme,
            'uri' => $this->uri,
            'path' => $this->path,
            'name' => $this->name,
            'description' => $this->description,
            'pathParams' => $serializeParams($this->pathParams),
            'queryParams' => $serializeParams($this->queryParams),
            'bodyParams' => $serializeParams($this->bodyParams),
            'headerParams' => $serializeParams($this->headerParams),
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
            $array['requires_auth']??false
        ))
            ->setParent($parent)
            ->setGlobals()
            ->setID($array['id']??null)
            ->setExamples($array['examples']??[]);
    }

}
