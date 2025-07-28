<?php

namespace Docsy\Utility\Importers;

use Docsy\Collection;
use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Utility\Enums\HTTPMethod;
use Docsy\Utility\Enums\ParamLocation;
use Docsy\Utility\Example;
use Docsy\Utility\Param;
use Docsy\Utility\Variable;
use Exception;

class Postman21Importer extends AbstractImporter
{
    protected static Collection $collection;

    /**
     * @throws Exception
     */
    static function import(array $options = [], string ...$files): Docsy
    {
        foreach ($files as $file) {
            if (!file_exists($file)) {
                syslog(LOG_ALERT, "The file [$file] does not exists.");
                continue;
            }

            $data = json_decode(file_get_contents($file), true);
            if (self::isValidPostmanJson($data, $file))
                \docsy()->addCollection(self::transformCollection($data, $options),override: true);
        }

        return \docsy();
    }

    /**
     * @throws Exception
     */
    private static function isValidPostmanJson(bool|array $data, string $file): bool
    {
        if (!is_array($data))
            throw new Exception("The data must be a valid json. Error in file [$file]");

        if (!isset($data['info']['schema']) || !str_contains($data['info']['schema'], "https://schema.getpostman.com/json/collection/v2.1.0"))
            throw new Exception("Only Postman JSON V2.1.0 is supported. Error in file [$file]");

        if (!isset($data['item']) || !is_array($data['item']))
            throw new Exception("The items must be an array. Error in file [$file]");

        return true;
    }

    protected static function transformCollection(array $data, array $options = []): Collection
    {
        self::$collection = new Collection(
            $data['info']['name'],
            $data['info']['description'],
            $data['info']['version'],
        );

        if (isset($data['variable']) && is_array($data['variable']))
            foreach ($data['variable'] as $variable)
                if (strtolower($variable['key']) === "base_url")
                    self::$collection->setBaseUrl($variable['value']);
                else
                    self::$collection->addVariable(self::transformVariable($variable, $options));

        if (isset($data['item']) && is_array($data['item']))
            foreach ($data['item'] as $item)
                self::$collection->add(self::transformContent($item, $options));

        return self::$collection;
    }

    protected static function transformVariable(array $variable, array $options = []): Variable
    {
        return new Variable(
            $variable['key'],
            $variable['value'],
            $variable['type'],
            $variable['description']
        );
    }

    protected static function transformFolder(array $folder, array $options = []): Folder
    {
        $class = new Folder(
            $folder['name'],
            $folder['description']
        );

        foreach ($folder['item'] as $item)
            $class->add(self::transformContent($item, $options));

        return $class;
    }

    protected static function transformRequest(array $request, array $options = []): Request
    {
        $uri = self::transformRequestUrl($request['request']['url'], $options);
        $queryParams = self::transformRequestQuery($request['request']['url']['query'], $options);

        $pathParams = self::transformRequestPath($request['request']['url']['variable'], $options) ?? [];

        $req = new Request(
            HTTPMethod::get($request['request']['method']),
            $uri,
            $request['name'],
            $request['description'],
            headerParams: self::transformRequestHeaders($request['request']['header'], $options),
            bodyParams: self::transformRequestBody($request['request']['body'], $options),
            queryParams: $queryParams,
            requires_auth: self::transformRequestAuth($request['request']['auth'], $options)
        );

        foreach ($pathParams as $pathParam) {
            $req->editParam(ParamLocation::Path, $pathParam['key'], $pathParam['description'], $pathParam['required'] ,$pathParam['value']);
        }

        if (!empty($request['request']['responses']))
            foreach ($request['request']['responses'] as $example)
                $req->addExample(self::transformRequestExamples($example, $options));

        return $req;
    }

    protected static function transformRequestUrl(array $url, array $options = []): string
    {
        return implode('/',
            array_merge(
                array_map(
                    function ($path_part) {
                        if (str_starts_with($path_part, ':')) {
                            return '{' . trim($path_part, ':') . '}';
                        }
                        return $path_part;
                    },
                    $url['path']
                )
            )
        );
    }

    protected static function transformRequestPath(array $pathParams, array $options = []): array | null
    {
        $params = array_merge(
            array_map(
                fn($path_param) => [
                    $path_param['key'] => $path_param,
                ],
                $pathParams)
        );
        return array_shift($params);
    }

    protected static function transformRequestQuery(array $queryParams, array $options = []): array
    {
        return array_merge(
            array_map(
                fn($param) =>
                    (new Param($param['key'], ParamLocation::Header, $param['description'] ?? "", required: $param['required'] ?? false, value:$param['value'] ?? ""))
                        ->is_disabled($param['disabled'] ?? false),
                $queryParams
            )
        );
    }

    protected static function transformRequestHeaders(array $headerParams, array $options = []): array
    {
        return array_merge(
            array_map(
                fn($param) =>
                    (new Param($param['key'], ParamLocation::Header, $param['description'] ?? "", required: $param['required'] ?? false, value:$param['value'] ?? ""))
                        ->is_disabled($param['disabled'] ?? false),
                $headerParams
            )
        );
    }

    protected static function transformRequestBody(array $body, array $options = []): array
    {
        if (!isset($body['mode']))
            return [];

        $mode = $body['mode'];
        $bodyParams = $body[$mode];

        return array_merge(
            array_map(
                fn($param) =>
                (new Param($param['key'], ParamLocation::Header, $param['description'] ?? "", required: $param['required'] ?? false, value:$param['value'] ?? ""))
                    ->is_disabled($param['disabled'] ?? false),
                $bodyParams
            )
        );
    }

    protected static function transformRequestAuth(array $auth, array $options = []): bool
    {
        if (!empty($auth)) {
            $access_token = $auth['bearer']['value'];

            if ($access_token !== '{{token}}')
                self::$collection
                    ->addGlobalHeader("Authorization", "Bearer $access_token", 'Authorization Token Header', true)
                    ->addVariable([
                        'name' => config('docsy.auth.token_variable_name'),
                        'value' => $access_token,
                        'description' => 'Authorization Token'
                    ]);

            return true;
        }
        return false;
    }

    protected static function transformRequestExamples(array $example, array $options = []): Example
    {
        $response = new \stdClass();
        $response->status = $example['status'];
        $response->code = $example['code'];
        $response->headers = array_merge(
            ...array_map(
                fn ($header) => [
                    $header['key'] => $header['value'],
                ],
                $example['header']
            )
        );
        $response->body = json_decode($example['body'], true);

        return new Example(
            self::transformRequest($example['originalRequest'], $options),
            $example['name'],
            "",
            $response
        );
    }

}