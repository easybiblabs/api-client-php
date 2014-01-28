<?php
require_once(__DIR__.'/vendor/autoload.php');
use fkooman\OAuth\Client\Api;
use fkooman\OAuth\Client\ClientConfig;
use fkooman\OAuth\Client\Context;
use fkooman\OAuth\Client\SessionStorage;
use Guzzle\Http\Client;

$yourClientId = 'foo';
$yourClientSecret = 'bar';
$configContext = 'easybib-api';
$apiRootUrl = 'https://data.playground.easybib.com';

$clientConfig = new ClientConfig([
    'client_id' => 'c2faee722f186bae7822d719aa64281fffc44ecb8805366a43d354c49b67cfa2',
    'client_secret' => 'bab2eae7dbba2e90f7a1c023c82e667d346ec9c560931aa90cc32401cf79460c',
    'authorize_endpoint' => $apiRootUrl . '/oauth/authorize',
    'token_endpoint' => $apiRootUrl . '/oauth/token',
]);

// a token store with PDO backend is also available; see
// https://github.com/easybiblabs/php-oauth-client#token-storage
$tokenStore = new SessionStorage();
$guzzleClient = new Client();
$oauth = new Api($configContext, $clientConfig, $tokenStore, $guzzleClient);

$context = new Context($yourClientId, ['USER_READ', 'DATA_READ_WRITE']);

 var_dump($oauth->getAuthorizeUri($context));
 exit;

use fkooman\OAuth\Client\Callback;

try {
    $callback = new Callback(
        $configContext,
        $clientConfig,
        new SessionStorage(),
        new \Guzzle\Http\Client()
    );

    $callback->handleCallback([
        'access_token' => 'd2a41736bce9388465f47715ee1ac362eebb73c1',
        'expires_in' => 3600,
        'token_type' => 'bearer',
        'scope' => 'USER_READ DATA_READ_WRITE',
        'refresh_token' => 'b9b858e2b6c4196477da0bc9df9058eb50cefff5',
        'state' => '14601156d07a0d0b',
    ]);
} catch (AuthorizeException $e) {
    // handle OAuth error, e.g. user refused to grant permission
}

// auth is good; redirect back to real content

use EasyBib\Api\Client\ApiTraverser;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;

$accessToken = $api->getAccessToken($context);
var_dump($accessToken);exit;

$authentication = new BearerAuth($accessToken->getAccessToken());

$guzzleClient = new Client($apiRootUrl);
$guzzleClient->addSubscriber($authentication);

$api = new ApiTraverser($guzzleClient);
$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
