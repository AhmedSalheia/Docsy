<?php

namespace Ahmedsalheia\Docsy;

use Ahmedsalheia\Docsy\traits\ArrayJsonSerialization;
use Ahmedsalheia\Docsy\traits\HasParent;
use GuzzleHttp\Exception\GuzzleException;
use function DeepCopy\deep_copy;

class DocsyExample implements \JsonSerializable
{
    use ArrayJsonSerialization;

    use HasParent;
    public DocsyRequest $request;
    public string $name;
    public array $response;
    public string $description;
    public string $file = '';

    public function __construct(?DocsyRequest $request, string $name, string $description = '', array $response = [])
    {
        $this->request = $request;
        $this->name = $name;
        $this->description = $description;
        $this->response = $response;
    }

    public function setResponse(array $response): static
    {
        $this->response = $response;
        return $this;
    }
    private function file(): string
    {
        if ($this->file !== '') return $this->file;

        $dir = config('docsy.examples_path');
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $file = "{$this->request->method}_{$this->request->name}_{$this->name}_.json";
        $this->file = "$dir/" . preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $file);

        return $this->file;
    }

    /**
     * @throws GuzzleException
     */
    public function runExample(bool $force = false): static
    {
        // Use cached if available
        if (!$force && file_exists($this->file()))
        {
            $data = json_decode(file_get_contents($this->file()), true);
            $this->setResponse($data['response']);
            return $this;
        }

        // Otherwise execute request
        $request = $this->buildHttpRequest();
        $this->setResponse($this->sendHttpRequest($request));

        // Store it
        file_put_contents($this->file(), json_encode($this, JSON_PRETTY_PRINT));

        return $this;
    }
    protected function buildHttpRequest(): array
    {
        $baseUrl = rtrim($this->request->getBaseUrl()??'', '/');
        $url = $baseUrl . '/' . ltrim($this->request->uri, '/');

        $headers = array_map(fn ($header) => $header->example, $this->request->headerParams);

        if (!empty($this->queryParams)) {
            $query = http_build_query(array_map(fn($p) => $p->example, $this->queryParams));
            $url .= '?' . $query;
        }

        $body = array_map(fn($p) => $p->example, $this->request->bodyParams);

        return [
            'method' => strtoupper($this->request->method->value),
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];
    }
    /**
     * @throws GuzzleException
     */
    protected function sendHttpRequest(array $request): array
    {
        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => $request['headers'],
        ];

        if (!empty($request['body'])) {
            $options['json'] = $request['body'];
        }

        $res = $client->request($request['method'], $request['url'], $options);

        return [
            'status' => $res->getStatusCode(),
            'headers' => $res->getHeaders(),
            'body' => json_decode((string)$res->getBody(), true),
        ];
    }

    public function toArray(): array
    {
        return [
            'class_name' => 'DocsyExample',
            'name' => $this->name,
            'description' => $this->description,
            'request' => $this->request,
            'response' => $this->response,
            'file' => $this->file
        ];
    }
    public static function fromArray(array $array, $parent = null): static
    {
        return (new self(DocsyRequest::fromArray($array['request']), $array['name'], $array['description']??'', $array['response']??[]))->setParent($parent);
    }
}