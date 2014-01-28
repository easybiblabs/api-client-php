# PHP Client for EasyBib API

Use this to request data from the [EasyBib API](https://data.easybib.com/).
The client uses [Guzzle](http://guzzlephp.org/) under the hood for the actual
HTTP calls, and [php-oauth-client](https://github.com/fkooman/php-oauth-client)
to manage the OAuth2 session.

More information on the OAuth2 session is available in [that project's
documentation](https://github.com/fkooman/php-oauth-client).

## Sample code

```php
use EasyBib\Api\Client\ApiTraverser;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use fkooman\OAuth\Client\Api;
use fkooman\OAuth\Client\ClientConfig;
use fkooman\OAuth\Client\Context;
use fkooman\OAuth\Client\SessionStorage;
use Guzzle\Http\Client;

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
// https://github.com/fkooman/php-oauth-client#token-storage
$tokenStore = new SessionStorage();
$oauth = new Api($configContext, $clientConfig, $tokenStore, $guzzleClient);

$context = new Context($yourClientId, ['USER_READ', 'DATA_READ_WRITE']);
$accessToken = $oauth->getAccessToken($context);

// NOTE: here, if no access token is found, you will need to redirect
// to $api->getAuthorizeUri($context) - see below for handling

$authentication = new BearerAuth($accessToken->getAccessToken());

$guzzleClient = new Client($apiRootUrl);
$guzzleClient->addSubscriber($authentication);

$api = new ApiTraverser($guzzleClient);
$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

If we did not find a current, valid token above, we would redirect the user to
the authentication endpoint on the OAuth service provider's server. After
authentication, the OAuth provider would redirect back to our specified
endpoint. We would then handle the request as follows:

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

    $cb->handleCallback($arrayOfQuerystringParams);
    // auth is good; redirect back to real content
} catch (AuthorizeException $e) {
    // handle OAuth error, e.g. user refused to grant permission
}
```

## License

This library is licensed under LGPL version 3.0

You can find more about this
license at http://www.gnu.org/licenses/lgpl-3.0.html
