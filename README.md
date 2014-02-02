# PHP Client for EasyBib API

Use this to request data from the [EasyBib API](https://data.easybib.com/).
The client uses [Guzzle](http://guzzlephp.org/) under the hood for the actual
HTTP calls.

## Installation

This library requires PHP 5.5 or later.

Use [Composer](https://getcomposer.org/) to add this project to your project's
dependencies.

Currently, only read access to the API is supported.

## Extension for your app

In order to use this client, you will need to implement several interfaces at
key integration points. See the OAuth2 documentation for details.

> TODO link to OAuth2 docs from above section

## Usage

First, instantiate the relevant objects.

> TODO fill this in

At this point you can access the EasyBib API:

> TODO finish this section

```php
$api = new ApiTraverser($oauthSession);
$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

## License

This library is licensed under the BSD 2-Clause License. Enjoy!

You can find more about this
license at http://opensource.org/licenses/BSD-2-Clause
