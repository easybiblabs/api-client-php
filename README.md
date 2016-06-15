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
        "url": "git@github.com:easybiblabs/api-client-php.git"
    }
],
"require": {
    "php":">=5.5.0",
    "easybib/api-client-php": "~0.2"
}
```

Once your JSON is set, this will install the package

```
composer.phar install
```

## Usage

### Instantiation

Two OAuth grant types are currently supported: JSON Web Token, and Authorization
Code.

#### JSON Web Token Grant

```php
use EasyBib\Api\Client\ApiBuilder;

// $redirector is your implementation of RedirectorInterface
$apiBuilder = new ApiBuilder();

$api = $apiBuilder->createWithJsonWebTokenGrant([
    'client_id' => 'client_123',
    'client_secret' => 'secret_987',
    'subject' => 'user_id_123',
]);

$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

#### Authorization Code Grant

In order to use an Authorization Code Grant, you will need an
implementation of `RedirectorInterface` from the OAuth2 Client library in order
to allow the API client to redirect the user and get back an authorization code.
You can find more information at an example in
[the README for that project](https://github.com/easybiblabs/oauth2-client-php#authorization-code-grant).

```php
use EasyBib\Api\Client\ApiBuilder;

$apiBuilder = new ApiBuilder();

// $redirector is your implementation of RedirectorInterface
$apiBuilder->setRedirector($redirector);

$api = $apiBuilder->createWithAuthorizationCodeGrant([
    'client_id' => 'client_123',
    'redirect_url' => 'http://myapp.example.com/handle-token-response',
]);

$user = $api->getUser();  // user serves as the entry point for traversing api resources
```

### Retrieving api resources

Once you have an API object, you can use it to traverse the API.
The two entry points are `$api->getUser()` and `$api->getProjects()`.

`getUser()` will return a `ApiResource` representing the user; calling
`getResourceData()->getRelations()` on the user api resource will return a set
of available references which can be called from the user. So the call chain
for a particular project's citations might be

```php
$api->getProjects()->get('project 123')->get('citations');
```

which would return a `Collection` of citations. Some more examples:

```php
$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getRelations();
$linksForSecondProject = $user->get('projects')[1]->listRelations();
$linksForSecondProject = $user->get('projects')[1]->hasRelation('citations');
$newProject = $user->post('projects', [
    'data' => [
        'name' => 'Some project',
        'defaultstyle' => 'mla',
    ]
]);
```

### Session backend

By default, the `ApiBuilder` uses native PHP sessions wrapped by a Symfony
session interface. You can implement a different session backend; see
[the Symfony docs](http://symfony.com/doc/current/components/http_foundation/sessions.html)
for more information.

```php
$apiBuilder = new ApiBuilder();
$session = new Session($myCustomBackend);
$apiBuilder->setSession($session);
```

### Resetting the session

It is possible to reset the token store of the OAuth2 client, such as when
a guest user registers or logs in, and needs a new token which will be
associated with the permanent user account. In this case, the application
consuming the API **must** instantiate a new `ApiTraverser` or call `setCache()`,
with a new cache or cache namespace, to ensure that the `ApiTraverser` will
not return api resources cached during the original session.

By default, `ApiTraverser` uses a PHP-array-based cache, so unless you have
implemented a different caching backend, you can simply instantiate a new
`ApiTraverser`, either directly or via the `ApiBuilder`.
