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

$tokenStore = new SessionStorage();
$oauth = new Api($configContext, $clientConfig, $tokenStore, $guzzleClient);

$context = new Context($yourClientId, ['USER_READ', 'DATA_READ_WRITE']);
$accessToken = $oauth->getAccessToken($context);

// NOTE: here, if no access token is found, you will need to redirect
// to $api->getAuthorizeUri($context)

$authentication = new BearerAuth($accessToken->getAccessToken());

$guzzleClient = new Client($apiRootUrl);

// this keeps Guzzle from throwing exceptions for HTTP 4XX and 5XX responses
$guzzleClient->setDefaultOption('exceptions', false);

$guzzleClient->addSubscriber($authentication);

$api = new ApiTraverser($guzzleClient);
$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

## License

This library is licensed under LGPL version 3.0

You can find more about this
license at http://www.gnu.org/licenses/lgpl-3.0.html
