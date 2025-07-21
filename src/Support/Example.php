<?php

namespace Docsy\Support;

use Docsy\Enums\ParamLocation;
use Docsy\Request;
use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\CouldBeDisabled;
use Docsy\Traits\HasID;
use Docsy\Traits\HasMeta;
use Docsy\Traits\HasParent;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonSerializable;
use stdClass;

class Example implements JsonSerializable
{
    use ArrayJsonSerialization, HasParent, HasID, CouldBeDisabled, HasMeta;

    public Request $request;
    public string $name;
    public ?stdClass $response;
    public string $description;
    public string $file = '';

    public int $execute_at;
    public int $response_time;

    public function __construct(?Request $request, string $name, string $description = '', stdClass $response = null)
    {
        $this->setID();
        $this->request = $request->setParent($this);
        $this->name = $name;
        $this->description = $description;
        $this->setResponse($response);
    }
    public function setResponse(stdClass | array | null $response): static
    {
        $this->response = $this->getResponseClass($response);
        return $this;
    }

    public function getResponseClass(stdClass | array | null $response): ?stdClass
    {
        if (!$response) return null;

        return is_object($response) ? $response : (object)$response;
    }

    private function file(): string
    {
        if ($this->file !== '') return $this->file;

        $dir = config('docsy.examples_path');
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $file = "{$this->request->method->value}_{$this->request->name}_{$this->name}_.json";
        $this->file = "$dir/" . preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $file);

        return $this->file;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function run(bool $force = false, bool $noCache = false): static
    {
        // Use cached if available
        if (!$force && file_exists($this->file()))
        {
            $data = json_decode(file_get_contents($this->file()), true);
            $this->setResponse($data['response']);
            return $this;
        }


        $is_auth_request = $this->request->id === $this->request->collection()?->getAuth()?->id;

        if ($is_auth_request)
            $this->prepareAuthParams();

        // execute request
        $this->execute();

        if ($this->response->code === 401 && config('docsy.auth.auto_run') && !$is_auth_request)
        {
            if(!$this->request->collection()->hasAuth())
                throw new Exception("No Auth Request Registered For {$this->request->collection()->name} Collection");

            $auth = $this->request->collection()->getAuth();
            $auth->run();

            $this->execute();
        }
        if ($is_auth_request)
        {
            $this->checkValidAuth()->injectAccessToken($this->getAccessToken());
        }

        // Cache it
        if (!$noCache) file_put_contents($this->file(), json_encode($this, JSON_PRETTY_PRINT));

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function buildHttpRequest(): array
    {
        $baseUrl = str_replace(["http://", "https://"],'',rtrim($this->request->getBaseUrl()??'', '/'));
        $url = $this->request->scheme . "://";

        if ($baseUrl) $url .= $baseUrl . '/';

        foreach ($this->request->path as $pathPart) {
            if (is_a($pathPart, Param::class))
                $url .= $pathPart->value . '/';
            elseif(!empty($pathPart)) $url .= ltrim($pathPart, '/') . '/';
        }
        $url = rtrim($url, '/');

        $this->request->setGlobals();

        $headers = array_merge(...array_values(array_map(fn ($header) => [$header->name => $header->value], $this->request->headerParams)));

        if (!empty($this->queryParams)) {
            $query = http_build_query(
                array_merge(
                    ...array_values(
                        array_map(
                            fn ($queryParam) => [$queryParam->name => $queryParam->value],
                            $this->request->queryParams
                        )
                    )
                )
            );
            $url .= '?' . $query;
        }

        $body = array_merge(
            ...array_values(
                array_map(
                    fn ($bodyParam) => [$bodyParam->name => $bodyParam->value],
                    $this->request->bodyParams
                )
            )
        );

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
    protected function sendHttpRequest(array $request): stdClass
    {
        $client = new Client();

        $options = [
            'headers' => $request['headers'],
        ];

        if (!empty($request['body'])) {
            $options['json'] = $request['body'];
        }
        $return = new stdClass();

        try {
            $res = $client->request($request['method'], $request['url'], $options);

            $this->setResponse(
                [
                    'status' => $res->getReasonPhrase(),
                    'code' => $res->getStatusCode(),
                    'headers' => $res->getHeaders(),
                    'body' => json_decode((string) $res->getBody(),true)
                ]
            );

        } catch (RequestException $e) {
            if ($e->hasResponse())
            {
                $this->setResponse(
                    [
                        'status' => $e->getResponse()->getReasonPhrase(),
                        'code' => $e->getResponse()->getStatusCode(),
                        'headers' => $e->getResponse()->getHeaders(),
                        'body' => json_decode((string)$e->getResponse()->getBody(), true)
                    ]
                );
            } else {
                $this->setResponse(
                    [
                        'status' => '',
                        'code' => $e->getCode(),
                        'headers' => [],
                        'body' => htmlentities($e->getMessage())
                    ]
                );
            }
        }

        return $return;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    protected function execute(): static
    {
        $request = $this->buildHttpRequest();
        $this->execute_at = time();
        $this->sendHttpRequest($request);
        $this->setResponse($this->response);
        $this->response_time = time() - $this->execute_at;

        return $this;
    }
    protected function prepareAuthParams(): static
    {
        $auth = $this->request;
        foreach (config('docsy.auth.default_credentials') as $key => $value) {
            if (!$auth->hasParam(ParamLocation::Body, $key)) {
                $auth->addBodyParam($key,required: true, value: $value);
            }
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function checkValidAuth() : static
    {
        $response = $this->response;
        if (is_null($response))
            throw new \Exception("No Auth Response Provided, Check your Auth Request {$this->request->getChain()}");

        if ($response->code !== 200)
            throw new \Exception("Auth Response Returned with {$response->code} {$response->status}, Check your Auth Request {$this->request->getChain()}");

        if (!$response->body)
            throw new \Exception("No Valid Response Body Provided, Check your Auth Request {$this->request->getChain()}");

        return $this;
    }
    /**
     * @throws Exception
     */
    protected function injectAccessToken($access_token): static
    {
        $this->request->addHeaderParam('Authorization','Authorization Token Header',true,"Bearer $access_token");
        $this->request->collection()
            ->addGlobalHeader("Authorization","Bearer $access_token",'Authorization Token Header',true)
            ->addVariable([
                'name' => config('docsy.auth.token_variable_name'),
                'value' => $access_token,
                'description' => 'Authorization Token'
            ]);
        return $this;
    }
    /**
     * @throws Exception
     */
    protected function getAccessToken() : string
    {
        $response = $this->response;
        if (is_null($response) && $this->request->id !== $this->request->collection()?->getAuth()?->id) // get from collection
            return $this->request->collection()->getAccessToken();

        $path = explode('.',config('docsy.auth.token_path'));
        $access_token = $response->body;
        foreach ($path as $pathPart)
        {
            if (array_key_exists($pathPart, $access_token))
                $access_token = $access_token[$pathPart];
            else
                throw new Exception("Can't find the access token using the path [".implode('.', $path)."], [$pathPart] doesn't seem to exist");
        }
        return trim((string)$access_token);
    }

    public function destroy(): void
    {
        if (file_exists($this->file()))
            unlink($this->file());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'class_name' => basename(get_class($this)),
            'name' => $this->name,
            'description' => $this->description,
            'request' => $this->request,
            'response' => $this->response,
            'file' => $this->file,
            'disabled' => $this->disabled,
            "executed_at" => $this->execute_at,
            "response_time" => $this->response_time
        ];
    }
    public static function fromArray(array $array, $parent = null): static
    {
        return (new self(
            Request::fromArray($array['request']),
            $array['name'],
            $array['description']??'',
            $array['response']??[])
        )
            ->setParent($parent)
            ->setID($array['id']??null)
            ->is_disabled($array['disabled']??false);
    }
}