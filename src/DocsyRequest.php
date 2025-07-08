<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\Enums\ParamLocation;
use Ahmedsalheia\Docsy\traits\HasParent;

class DocsyRequest
{
    use HasParent;
    public string $method;
    public string $uri;
    public string $name = '';
    public string $description = '';
    /** @var DocsyParam[] */
    public array $urlParams = [];
    /** @var DocsyParam[] */
    public array $queryParams = [];
    /** @var DocsyParam[] */
    public array $body = [];
    public array $headers = [];
    public bool $requires_auth = false;

    /**
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param DocsyParam[] $bodyParams
     * @param DocsyParam[] $queryParams
     * @param bool $requires_auth
     * @throws \Exception
     */
    public function __construct(string $method, string $uri, string $name = '', string $description = '', array $headers = [], array $bodyParams = [], array $queryParams = [], bool $requires_auth = false) {
        $this->method = $method;

        // adding url params
        preg_match_all('/\{[\w\W\d]+(\?)*\}/', $uri, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $urlParam = str_replace(['{', '}'], '', $match);
                $required = !str_ends_with($match, '?');

                $this->addUrlParam(trim($urlParam,'?'), required: $required);
            }
        }

        $this->headers = $headers;
        foreach ($bodyParams as $param) {
            if (!$param instanceof DocsyParam) {
                throw new \InvalidArgumentException("All Body Params must be instances of DocsyParam.");
            }
            $this->addBodyParam($param->name, $param->description, $param->type, $param->required, $param->example);
        }

        // adding query Params
        foreach ($queryParams as $param) {
            if (!$param instanceof DocsyParam) {
                throw new \InvalidArgumentException("All Query Params must be instances of DocsyParam.");
            }
            $this->addQueryParam($param);
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

        $this->requires_auth = $requires_auth;
        if ($requires_auth) {
            $this->headers = array_merge($this->headers, ['Authorization' => 'Barrier {{token}}']);
        }

        // cleaning up the url and making the url params values
        $this->uri = explode('?', str_replace(['?}','{','}'], ['}','{{','}}'], $uri))[0];

        // add name and description
        $this->name = $name ?: $this->uri;
        $this->description = $description;
    }
    public function setGlobals(array $globals): static
    {
        $this->headers = array_merge($this->headers, $globals['headers'] ?? []);
        $this->body = array_merge($this->body, $globals['body'] ?? []);
        $this->queryParams = array_merge($this->queryParams, $globals['query'] ?? []);

        return $this;
    }

    public function addUrlParam($name, string $description = "", string $type = "", bool $required = false, mixed $example = ''): static
    {
        $this->urlParams[$name] = new DocsyParam($name, ParamLocation::Path, $description, $type, $required, $example);
        return $this;
    }
    public function addQueryParam($name, string $description = "", string $type = "", bool $required = false, mixed $example = ''): static
    {
        $this->queryParams[$name] = new DocsyParam($name, ParamLocation::Query, $description, $type, $required, $example);
        return $this;
    }

    public function addBodyParam($name, string $description = "", string $type = "", bool $required = false, mixed $example = '') : static
    {
        $this->body[$name] = new DocsyParam($name, ParamLocation::Body, $description, $type, $required, $example);
        return $this;
    }
}
