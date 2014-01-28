# PHP Client for EasyBib API

Use this to request data from the [EasyBib API](https://data.easybib.com/).
The client uses [Guzzle](http://guzzlephp.org/) under the hood for the actual
HTTP calls, and [php-oauth-client](https://github.com/easybiblabs/php-oauth-client)
to manage the OAuth2 session.

More information on the OAuth2 session is available in [that project's
documentation](https://github.com/easybiblabs/php-oauth-client).

## Installation

Use [Composer](https://getcomposer.org/) to add this project to your project's
dependencies.

## Usage

Currently, only read access is supported.

Here is how to instantiate the core objects for the client:

```php
use fkooman\OAuth\Client\Api;
use fkooman\OAuth\Client\ClientConfig;
use fkooman\OAuth\Client\Context;
use fkooman\OAuth\Client\SessionStorage;

$yourClientId = 'foo';
$yourClientSecret = 'bar';
$configContext = 'easybib-api';
$apiRootUrl = 'https://data.easybib.com';

$clientConfig = new ClientConfig([
    'client_id' => $yourClientId,
    'client_secret' => $yourClientSecret,
    'authorize_endpoint' => $apiRootUrl . '/oauth/authorize',
    'token_endpoint' => $apiRootUrl . '/oauth/token',
]);

// a token store with PDO backend is also available; see
// https://github.com/easybiblabs/php-oauth-client#token-storage
$tokenStore = new SessionStorage();
$oauth = new Api($configContext, $clientConfig, $tokenStore, $guzzleClient);

$context = new Context($yourClientId, ['USER_READ', 'DATA_READ_WRITE']);
```

Next, your application will redirect the user to the EasyBib OAuth
authorization endpoint
so that the user can approve the request for access.

The EasyBib OAuth service will redirect the user back to your application
with the user's token. Your application should handle that request as follows:

```php
// assuming same config variables as above

use fkooman\OAuth\Client\Callback;

try {
    $callback = new Callback(
        $configContext,
        $clientConfig,
        new SessionStorage(),
        new \Guzzle\Http\Client()
    );

    $callback->handleCallback($_GET);
} catch (AuthorizeException $e) {
    // handle OAuth error, e.g. user refused to grant permission
}

// auth is good; redirect back to real content
```

At this point you can access the EasyBib API:

```php
use EasyBib\Api\Client\ApiTraverser;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use Guzzle\Http\Client;

$accessToken = $api->getAccessToken($context);
$authentication = new BearerAuth($accessToken->getAccessToken());

$guzzleClient = new Client($apiRootUrl);
$guzzleClient->addSubscriber($authentication);

$api = new ApiTraverser($guzzleClient);
$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

## License

This library is licensed under the BSD 2-Clause License. Enjoy!

You can find more about this
license at http://opensource.org/licenses/BSD-2-Clause
