<?php

namespace Docsy\Support;

use Docsy\Enums\ParamLocation;
use Docsy\Request;
use Docsy\Traits\ArrayJsonSerialization;
use Docsy\Traits\CouldBeDisabled;
use Docsy\Traits\HasID;
use Docsy\Traits\HasParent;
use GuzzleHttp\Exception\GuzzleException;

class Example implements \JsonSerializable
{
    use ArrayJsonSerialization, HasParent, HasID, CouldBeDisabled;

    public Request $request;
    public string $name;
    public ?\stdClass $response;
    public string $description;
    public string $file = '';

    public $excuted_at;
    public $response_time;

    public function __construct(?Request $request, string $name, string $description = '', \stdClass $response = null)
    {
        $this->setID();
        $this->request = $request->setParent($this);
        $this->name = $name;
        $this->description = $description;
        $this->response = $response;
    }
    public function setResponse(\stdClass | array $response): static
    {
        if (is_array($response)) {
            $arr = $response;
            $response = new \stdClass();
            $response->status = $arr['status'];
            $response->status_code = $arr['status_code'];
            $response->headers = $arr['headers'];
            $response->body = $arr['body'];
        }
        $this->response = $response;
        return $this;
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

        // execute request
        $request = $this->buildHttpRequest();
        $this->excuted_at = time();
        $response = $this->sendHttpRequest($request);

        if ($response->code === 401 && config('docsy.auth.auto_run'))
        {
            $this->runAuth();
            $response = $this->sendHttpRequest($request);
        }

        $this->setResponse($response);
        $this->response_time = time() - $this->excuted_at;


        // Cache it
        if (!$noCache) file_put_contents($this->file(), json_encode($this, JSON_PRETTY_PRINT));

        return $this;
    }
    protected function buildHttpRequest(): array
    {
        $baseUrl = str_replace(['http://','https://'], '', rtrim($this->request->getBaseUrl()??'', '/'));
        $url = $this->request->scheme . "://";

        if ($baseUrl) $url .= $baseUrl . '/';

        foreach ($this->request->path as $pathPart) {
            if (is_a($pathPart, Param::class))
                $url .= $pathPart->value . '/';
            elseif(!empty($pathPart)) $url .= ltrim($pathPart, '/') . '/';
        }
        $url = rtrim($url, '/');

        $headers = array_map(fn ($header) => $header->value, $this->request->headerParams);

        if (!empty($this->queryParams)) {
            $query = http_build_query(array_map(fn($p) => $p->example, $this->queryParams));
            $url .= '?' . $query;
        }

        $body = [];
        array_walk($this->request->bodyParams, function ($p) use (&$body) {
            $body[$p->name] = $p->value;
        });

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
    protected function sendHttpRequest(array $request): \stdClass
    {
        $client = new \GuzzleHttp\Client();

        $options = [
            'headers' => $request['headers'],
        ];

        if (!empty($request['body'])) {
            $options['json'] = $request['body'];
        }
        $return = new \stdClass();

        try {
            $res = $client->request($request['method'], $request['url'], $options);

            $return->status = $res->getReasonPhrase();
            $return->status_code = $res->getStatusCode();
            $return->headers = $res->getHeaders();
            $return->body = json_decode((string)$res->getBody(), true);

        } catch (\Exception $e) {
            $return->status = htmlentities($e->getMessage());
            $return->status_code = $e->getCode();
            $return->headers = [];
            $return->body = null;
        }

        return $return;
    }
    private function runAuth() : void
    {
        if(!$this->request->collection()->hasAuth())
            throw new \Exception("No Auth Request Registered For {$this->request->collection()->name} Collection");

        $access_token = $this->request->collection()->getAuthToken();

        if (is_null($access_token))
        {
            $auth = $this->request->collection()->getAuth();
            foreach (config('docsy.auth.default_credentials') as $key => $value) {
                if (!$auth->hasParam(ParamLocation::Body, $key))
                    $auth->addParam(ParamLocation::Body, $key, value: $value);
                else
                    $auth->editParam(ParamLocation::Body, $key, value: $value);
            }

            $response = $auth->run()->response;

            echo '<pre>Auth Response: ';
            var_dump($response);
            echo '</pre>';

            if (is_null($response))
                throw new \Exception("No Auth Response Provided, Check your Auth Request {$this->request->getChain()}");

            if ($response->code !== 200)
                throw new \Exception("Auth Response Returned with {$response->code} {$response->status}, Check your Auth Request {$this->request->getChain()}");

            if (!$response->body)
                throw new \Exception("No Valid Response Body Provided, Check your Auth Request {$this->request->getChain()}");

            $path = config('docsy.examples_path');
            $access_token = $response->body;
            foreach ($path as $pathPart)
            {
                $access_token = $access_token[$pathPart];
            }

            $this->request->addHeaderParam('Authorization','Authorization Token Header',true,"Bearer $access_token");
            $this->request->collection()
                ->addGlobalHeader("Authorization","Bearer $access_token",'Authorization Token Header',true)
                ->addVariable([
                    'name' => config('docsy.auth.token_variable_name'),
                    'value' => $access_token,
                    'description' => 'Authorization Token'
                ]);

        }
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
            "executed_at" => $this->excuted_at,
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