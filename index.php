<?php

require __DIR__ . '/vendor/autoload.php';

use Ahmedsalheia\Docsy\Docsy;
use Ahmedsalheia\Docsy\DocsyRequest;
use Ahmedsalheia\Docsy\DocsyFolder;

$docsy = new Docsy();

$docsy->addCollection('Collection01', 'This is a default Collection')
    ->addCollection('Collection02', 'This is a default Collection');

$docsy->collection()
    ->addVariables([
        'base_url' => [
            'value' => 'https://api.example.com',
            'type' => 'string',
            'description' => 'Base URL of the API'
        ],
        'api_key' => [
            'value' => 'xyz123',
            'type' => 'secret',
            'description' => 'Personal API token'
        ]
    ])->addVariable('token', [
        'value' => 'exsklasgpajslgasgjalsgafasfslajslgjlaj',
        'type' => 'secret',
        'description' => 'Authorization token'
    ])->addGlobalHeader('Accept', 'application/json')
    ->addGlobalHeader('Content-Type', 'application/json')
    ->addGlobalHeader('Access-Control-Allow-Origin', 'https://api.example.com')
    ->addGlobalQueryParam('sort', '-age', 'This is a sort query param', false)
    ->add(
        (new DocsyRequest('get','/api/data/{resource?}', 'Get Resource Data', requires_auth: true))
            ->addUrlParam('resource', 'This is the resource you want data for', example: "users")
    )->add(
        (new DocsyFolder('User', requires_auth: true))
            ->add(new DocsyRequest('get','/api/user/data?sort=-age&filter=age,25', 'Get User Data with sorting and filters'))
            ->add(
                (new DocsyFolder('Auth'))
                    ->add(new DocsyRequest('get','/api/user/login','Login'))
                    ->add(new DocsyRequest('get','/api/user/register','Register'))
            )
    );

// exporting:
//$docsy->collection()->savePostmanAs('./exports/postman_collection.json');
//$docsy->collection()->saveOpenApiAs('./exports/openapi_collection.json');
//$docsy->collection()->saveOpenApiAs('./exports/openapi_collection.yaml');

foreach ($docsy->collections() as $collection)
{
    echo '<pre>';
    var_dump($collection->toPostmanJson());
    echo '</pre><br><br>';
}