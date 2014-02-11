# PHP Client for EasyBib API

Use this to request data from the [EasyBib API](https://data.easybib.com/).
The client uses [Guzzle](http://guzzlephp.org/) under the hood for the actual
HTTP calls.

## Installation

This library requires PHP 5.5 or later.

Use [Composer](https://getcomposer.org/) to add this project to your project's
dependencies.

### Your Composer JSON


```json
"repositories":[
    {
        "type": "vcs",
        "url": "git@github.com:easybib/api-client-php.git"
    }
],
"require": {
    "php":">=5.5.0",
    "easybib/api-client-php": "dev-master"
}
```

Once your JSON is set, this will install the package

```
composer.phar install
```

## Usage

If you are going to use an Authorization Code Grant, you will need an
implementation of `RedirectorInterface` from the OAuth2 Client library in order
to allow the API client to redirect the user and get back an authorization code.
You can find more information at an example in
[the README for that project](https://github.com/easybiblabs/oauth2-client-php#authorization-code-grant).

You can then call the API:

```php
use EasyBib\Api\Client\ApiBuilder;

// $redirector is your implementation of RedirectorInterface
$apiBuilder = new ApiBuilder($redirector);

$api = $apiBuilder->createWithAuthorizationCodeGrant([
    'client_id' => 'client_123',
    'redirect_url' => 'http://myapp.example.com/handle-token-response',
]);

$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

## License

This library is licensed under the BSD 2-Clause License. Enjoy!

You can find more about this
license at http://opensource.org/licenses/BSD-2-Clause
