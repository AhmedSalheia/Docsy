<?php

require __DIR__ . '/vendor/autoload.php';

use Ahmedsalheia\Docsy\Docsy;
use Ahmedsalheia\Docsy\DocsyParam;
use Ahmedsalheia\Docsy\DocsyRequest;
use Ahmedsalheia\Docsy\DocsyFolder;
use Ahmedsalheia\Docsy\Enums\ParamLocation;

//$docsy = new Docsy();
//
//$docsy->addCollection('Collection01', 'This is a default Collection')
//    ->addCollection('Collection02', 'This is a default Collection');
//
//$docsy->collection()
//    ->setVersion('1.1.0')
//    ->setBaseUrl('https://api.example.com')
//    ->addVariables([
//        'api_key' => [
//            'value' => 'xyz123',
//            'type' => 'secret',
//            'description' => 'Personal API token'
//        ]
//    ])->addVariable('token', [
//        'value' => 'exsklasgpajslgasgjalsgafasfslajslgjlaj',
//        'type' => 'secret',
//        'description' => 'Authorization token'
//    ])->addGlobalHeader('Accept', 'application/json')
//    ->addGlobalHeader('Content-Type', 'application/json')
//    ->addGlobalHeader('Access-Control-Allow-Origin', 'https://api.example.com')
//
//    ->addGlobalQueryParam('sort', '-age', 'This is a sort query param', false)
//
//    ->add(
//        (new DocsyRequest('get','/api/data/{resource?}', 'Get Resource Data', requires_auth: true))
//            ->addPathParam('resource', 'This is the resource you want data for', example: "users")
//    )->add(
//        (new DocsyFolder('User', requires_auth: true))
//            ->add(new DocsyRequest('get','/api/user/data?sort=-age&filter=age,25', 'Get User Data with sorting and filters'))
//            ->add(
//                (new DocsyFolder('Auth'))
//                    ->add(new DocsyRequest('get','/api/user/login','Login'))
//                    ->add(new DocsyRequest('get','/api/user/register','Register'))
//            )
//    );

// exporting:
//$docsy->export('json', true);
//$docsy->collection()->savePostmanAs('./exports/postman_collection.json');
//$docsy->collection()->saveOpenApiAs('./exports/openapi_collection.json');
//$docsy->collection()->saveOpenApiAs('./exports/openapi_collection.yaml');

// importing:


//foreach ($docsy->collections() as $collection)
//{
//    echo '<pre>';
//    var_dump($collection->toPostmanJson());
//    echo '</pre><br><br>';
//}

// sending API request:

//try {
//    $addRequest = (new DocsyRequest(
//        'POST',
//        '/api/customer/add',
//        'Add Customer',
//        'Adds a new customer to the database',
//        headers: [
//            [
//                'name' => 'Content-Type',
//                'example' => 'application/json',
//                'description' => '',
//                'required' => true,
//            ],
//            [
//                'name' => 'Accept',
//                'example' => 'application/json',
//                'description' => '',
//                'required' => true,
//            ]
//        ],
//        bodyParams: [
//            [
//                'name' => 'first_name',
//                'example' => 'John',
//                'description' => 'Customer first name',
//                'required' => false,
//            ],
//            [
//                'name' => 'last_name',
//                'example' => 'Doe',
//                'description' => 'Customer last name',
//                'required' => false,
//            ],
//            [
//                'name' => 'phone',
//                'example' => '+123456789',
//                'description' => 'Customer phone number',
//                'required' => false,
//            ],
//            [
//                'name' => 'email',
//                'example' => 'JohnDoe@email.com',
//                'description' => 'Customer email address',
//                'required' => false,
//            ],
//            [
//                'name' => 'address',
//                'example' => 'Palestine',
//                'description' => 'Customer address',
//                'required' => false,
//            ],
//            [
//                'name' => 'city',
//                'example' => 'Gaza',
//                'description' => 'Customer city',
//                'required' => false,
//            ],
//            [
//                'name' => 'state',
//                'example' => 'Gaza Strip',
//                'description' => 'Customer state',
//                'required' => false,
//            ]
//        ],
//        queryParams: [
//            [
//                'name' => 'sort',
//                'example' => '-age',
//                'description' => 'A sort query param',
//                'required' => false
//            ]
//        ],
//        requires_auth: false
//    ))
//        ->snapResponse('Example01', false, 'This is an Example')
//        ->snapResponse('Example02', false, 'This is another Example', ['body' => ["city" => "Khan Yunis"]]);
//} catch (\GuzzleHttp\Exception\GuzzleException $e) {
//
//}
//
//try {
    $getRequest = (new DocsyRequest(
        'GET',
        '/api/customers',
        'Get All Customer',
        'Get all customers from the database',
        headerParams: [
            [
                'name' => 'Content-Type',
                'example' => 'application/json',
                'description' => '',
                'required' => true,
            ],
            [
                'name' => 'Accept',
                'example' => 'application/json',
                'description' => '',
                'required' => true,
            ]
        ],
        requires_auth: false
    ))->snapResponse('Example02', true);

    echo '<pre>';
    print_r($getRequest->getParams(ParamLocation::Header));
    echo '</pre>';

//} catch (\GuzzleHttp\Exception\GuzzleException $e) {
//}