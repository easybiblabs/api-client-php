# PHP Client for EasyBib API

Use this to request data from the [EasyBib API](https://data.easybib.com/).
The client uses [Guzzle](http://guzzlephp.org/) under the hood for the actual
HTTP calls.

## Installation

Use [Composer](https://getcomposer.org/) to add this project to your project's
dependencies.

Currently, only read access to the API is supported.

## Extension for your app

In order to use this client, you will need to implement several interfaces at
key integration points.

### Token storage

Tokens might be stored in session or in a database. For a session implementation,
you might use something like the following pseudocode:

```php
class SessionTokenStore implements \EasyBib\Api\Client\Session\TokenStore\TokenStoreInterface
{
    public function getToken()
    {
        return $this->mySessionWrapper->get('easybib.api.token');
    }

    public function setToken($token)
    {
        $this->mySessionWrapper->set('easybib.api.token', $token);
    }

    public function setExpirationTime($time)
    {
        $this->mySessionWrapper->expireAt($time);
    }
}
```

### Redirection

To make the initial authorization call, your app must redirect the user's
browser to EasyBib's authorization page for confirmation. Your application's
redirect mechanism must be injected via something like this pseudocode:

```php
class MyRedirector implements \EasyBib\Api\Client\Session\RedirectorInterface
{
    public function redirect($url)
    {
        // throws exception or calls header() to redirect user
        $this->myResponseWrapper->redirect($url);
    }
}
```

## Usage

When you are ready to connect to the EasyBib API, you will need to authorize
your user.

> TODO fill me in

The EasyBib OAuth service will redirect the user back to your application
with the user's token. Your application should handle that request as follows:

> TODO fill me in

At this point you can access the EasyBib API:

> TODO finish this section

```php
$api = new ApiTraverser($apiSession);
$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

## License

This library is licensed under the BSD 2-Clause License. Enjoy!

You can find more about this
license at http://opensource.org/licenses/BSD-2-Clause
