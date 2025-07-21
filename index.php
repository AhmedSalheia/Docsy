<?php

require __DIR__ . '/vendor/autoload.php';

use Docsy\Docsy;
use Docsy\Folder;
use Docsy\Request;
use Docsy\Enums\ParamLocation;

$docsy = Docsy::getInstance();

$docsy->addCollection('Collection01', 'This is a default Collection')
    ->addCollection('Collection02', 'This is a default Collection');

$docsy->getCollection()
    ->setVersion('1.1.0')
    ->setBaseUrl('http://slimapp.test/api')
    ->addVariables([
        [
            'name' => 'api_key',
            'value' => 'xyz123',
            'type' => 'secret',
            'description' => 'Personal API token',
            'disabled' => true
        ]
    ])->addVariable([
        "name" => 'token',
        'value' => 'exsklasgpajslgasgjalsgafasfslajslgjlaj',
        'type' => 'secret',
        'description' => 'Authorization token'
    ])->addGlobalHeader('Accept', 'application/json')
    ->addGlobalHeader('Content-Type', 'application/json')
    ->addGlobalHeader('Access-Control-Allow-Origin', 'https://api.example.com')

    ->addGlobalQueryParam('sort', '-age', 'This is a sort query param')

    ->add(
        (new Request('get','/api/data/{resource?:something here}?sort=-age', 'Get Resource Data', requires_auth: true))
            ->editParam(ParamLocation::Path,'resource','the resource to get data for',true,false)
    )->add(
        (new Folder('User', requires_auth: true))
            ->add(new Request('get','/api/user/data?sort=-age&filter=age,25', 'Get User Data with sorting and filters'))
            ->add(
                (new Folder('Auth'))
                    ->add(new Request('get','/api/user/login','Login'))
                    ->add(new Request('get','/api/user/register','Register'))
            )
    )->add(
        (new Folder('User', requires_auth: true))
            ->add(new Request('get','/api/user/data?sort=-age&filter=age,25', 'Get User Data with sorting and filters'))
            ->add(
                (new Folder('Auth'))
                    ->add($auth = (new Request('get','/auth','auth'))->asAuth())
                    ->add($requires_auth_req = (new Request('get','/requires_auth','requires_auth')))
            )
    );

// check auth flow:
dump($requires_auth_req->run()->response);

// getting collections by id, name or chain
//$userFolders = array_keys($docsy->getCollection()->get('User'));
//dump($docsy->getCollection()->get($userFolders[0] . '.Auth.Login'));

// summarizing the docsy app:
//dump($docsy->summary());

// exporting:
//$docsy->export('json', true);
//$docsy->collection()->savePostmanAs('./exports/postman_collection.json');
//$docsy->collection()->saveOpenApiAs('./exports/openapi_collection.json');
//$docsy->collection()->saveOpenApiAs('./exports/openapi_collection.yaml');

// importing:
//$docsy->import('json','./exports/Docsy_2025_07_17_01_44_02');

// sending API request:

//$addRequest = (new Request(
//    'POST',
//    'http://slimapp.test/api/customer/add',
//    'Add Customer',
//    'Adds a new customer to the database',
//    headerParams: [
//        [
//            'name' => 'Content-Type',
//            'value' => 'application/json',
//            'description' => '',
//            'required' => true,
//        ],
//        [
//            'name' => 'Accept',
//            'value' => 'application/json',
//            'description' => '',
//            'required' => true,
//        ]
//    ],
//    bodyParams: [
//        [
//            'name' => 'first_name',
//            'value' => 'John',
//            'description' => 'Customer first name',
//            'required' => false,
//        ],
//        [
//            'name' => 'last_name',
//            'value' => 'Doe',
//            'description' => 'Customer last name',
//            'required' => false,
//        ],
//        [
//            'name' => 'phone',
//            'value' => '+123456789',
//            'description' => 'Customer phone number',
//            'required' => false,
//        ],
//        [
//            'name' => 'email',
//            'value' => 'JohnDoe@email.com',
//            'description' => 'Customer email address',
//            'required' => false,
//        ],
//        [
//            'name' => 'address',
//            'value' => 'Palestine',
//            'description' => 'Customer address',
//            'required' => false,
//        ],
//        [
//            'name' => 'city',
//            'value' => 'Gaza',
//            'description' => 'Customer city',
//            'required' => false,
//        ],
//        [
//            'name' => 'state',
//            'value' => 'Gaza Strip',
//            'description' => 'Customer state',
//            'required' => false,
//        ]
//    ],
//    queryParams: [
//        [
//            'name' => 'sort',
//            'value' => '-age',
//            'description' => 'A sort query param',
//            'required' => false
//        ]
//    ],
//    requires_auth: false
//))
//    ->snapExample('Example01', true, 'This is an Example')
//    ->snapExample('Example02', true, 'This is another Example', ['body' => ["city" => "Khan Yunis"]]);
//
//$getRequest = (new Request(
//    'GET',
//    'http://slimapp.test/api/customers',
//    'Get All Customer',
//    'Get all customers from the database',
//    headerParams: [
//        [
//            'name' => 'Content-Type',
//            'value' => 'application/json',
//            'description' => '',
//            'required' => true,
//        ],
//        [
//            'name' => 'Accept',
//            'value' => 'application/json',
//            'description' => '',
//            'required' => true,
//        ]
//    ],
//    requires_auth: false
//))->snapExample('Example02', true);

//echo '<pre>';
//print_r($getRequest->getParams(ParamLocation::Header));
//echo '</pre>';