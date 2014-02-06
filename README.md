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
        "url": "git@github.com:easybiblabs/oauth2-client-php.git"
    },
],
"require": {
    "php":">=5.5.0",
    "easybib/api-client-php": "dev-master"
},
```

Once your JSON is set, this will install the package

```
composer.phar install
```

## Usage

You will need an OAuth client session configured for the EasyBib Api. You can find
an example in [the tests](tests/EasyBib/Tests/Api/Client/Given.php)
on the `iHaveRegisteredWithAnAuthCodeSession()` method, and much more documentation
in [the OAuth client repo's documentation](http://github.com/easybiblabs/oauth2-client-php).

With your OAuth client, you can then call the API:

```php
// instantiate $oauthSession, and then:

$apiHttpClient = new \Guzzle\Http\Client('https://data.easybib.com');
$oauthSession->addResourceClient($apiHttpClient);
$api = new ApiTraverser($apiHttpClient);

$user = $api->getUser();  // user serves as the entry point for traversing resources

$titleOfFirstProject = $user->get('projects')[0]->title;
$citationsFromFirstProject = $user->get('projects')[0]->get('citations');
$linksForSecondProject = $user->get('projects')[1]->getLinkRefs();
```

## License

This library is licensed under the BSD 2-Clause License. Enjoy!

You can find more about this
license at http://opensource.org/licenses/BSD-2-Clause
