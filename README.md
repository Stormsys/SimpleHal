# SimpleHAL

SimpleHal is an easy to use library for consuming Hal API's.

## Installation using Composer

Add the dependency:

```bash
composer require stormsys/simplehal
```

## Limitations

Currently the library supports only GET requests, there are plans to add support for PUT, POST and DELETE in the future.

## Usage

### Setup Root Resource

This examples shows how to setup a root resource to begin nagivation.

```php
<?php

use Guzzle\Http\Client;
use Stormsys\SimpleHal\Clients\GuzzleHalClient;
use Stormsys\SimpleHal\Resource;
use Stormsys\SimpleHal\Uri\GuzzleUriTemplateProcessor;
use Stormsys\SimpleHal\Uri\LeagueUriJoiner;

$client = new GuzzleHalClient(new Client());
$uriTemplateProcessor = new GuzzleUriTemplateProcessor();
$uriJoiner = new LeagueUriJoiner();
$apiRootUrl = 'http://haltalk.herokuapp.com';

$root = new Resource($client, $uriTemplateProcessor, $uriJoiner, $apiRootUrl);
```

### Following Non-Templated Links

once you have obtained a resource, SimpleHal offers several ways to follow links.

```php
$latestPosts = $root->follow('ht:latest-posts');
```

Simple hal offers powerful php overloading mechanisms(see note on magic methods below) for making the api more fluent, the line above can also be represented as such:

```php
$latestPosts = $root->{'ht:latest-posts'};
$latestPosts = $root->{'ht:latest-posts'}();
```

### Following Templated Links

Similar to the above, you can follow templated links by providing the template variables like so.

```php
$john = $root->follow('ht:me', ['name' => 'john']);
```

and again with the overload(see note on magic methods below). 
```php
$john = $root->{'ht:me'}(['name' => 'john']);
```


### Reading Embedded Resources

Sometimes partial, incomplete or full resources are embedded into the hal document, these are accessible using the embedded function like so.

```php
$postsArray = $latestPosts->embedded('ht:post');
```

Magic acessors are also avaliable for embedded resources (see magic methods below).

```php
$postsArray = $latestPosts->{'ht:post'};
$postsArray = $latestPosts->{'ht:post'}();
```


##### Reading Resource Properties
To access properties of a resource, you can use the prop method shown below.

```php
$johnsRealName = $john->prop('real_name');
```

As eith following relations and embedded resources, magic acessors are avalible for properties (see magic methods below).

below are all equivlant ways to access the property.

```php
$johnsRealName = $john->real_name;
$johnsRealName = $john->{'real_name'};
$johnsRealName = $john->{'real_name'}();
$johnsRealName = $john->real_name();
```

### Refresh / Obtain Full Representations

You can update or refresh resources that you have already loaded by calling refresh()

```php
$latestPosts = $latestPosts->refresh();
```

or in the event that you are using a embedded partial resource as long as a self link is present you can use, this is just an alias for ->refresh().
```php
$firstPost = $postsArray[0]->full();
```

### Chain Example

The example below shows how you might chain methods to obtain some data on a hal api.

```php
$postBody = $root->{'ht:latest-posts'}->{'ht:post'}[0]->content;
$postBody = $root->follow('ht:latest-posts')->{'ht:post'}[0]->content;
$postBody = $root->follow('ht:latest-posts')->embedded('ht:post')[0]->content;
$postBody = $root->follow('ht:latest-posts')->embedded('ht:post')[0]->prop('content');
```

### Magic Methods

You can access embedded resources, follow links and access proprties through the magic acessors and method overloads. the name of the field/method will be equal to the relation or the property name.

the order in which SimpleHal will attempt to resolve the request is:
* Embedded Resources by Link Relation
* Follow link to obtain Resource if link relation exists
* Return property of resource
* if none of the above, null

is it impoarant to understand the order which requests are resolved as the library will pick the first one that is found, in the event of duplicates an embedded resource will be picked over the others, and following a link before a property.

### Interfaces
The library offer the following interfaces which can be custom implemented.

* Stormsys\SimpleHal\Uri\UriTemplateProcessorInterface
* Stormsys\SimpleHal\Uri\UriJoinerInterface
* Stormsys\SimpleHal\Clients\HalClientInterface


by default the library has bundled the following implementations:
* Stormsys\SimpleHal\Uri\GuzzleUriTemplateProcessor
* Stormsys\SimpleHal\Uri\LeagueUriJoiner
* Stormsys\SimpleHal\Clients\GuzzleHalClient
* Stormsys\SimpleHal\Clients\FileGetContentsHalClient


## TODO

* Add Tests
* Support for Persist (POST/PUT)
* Support for Delete
