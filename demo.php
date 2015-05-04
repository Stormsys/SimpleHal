<?php

include('./vendor/autoload.php');

use GuzzleHttp\Client;
use Stormsys\SimpleHal\Clients\GuzzleHalClient;
use Stormsys\SimpleHal\Resource;
use Stormsys\SimpleHal\Uri\GuzzleUriTemplateProcessor;
use Stormsys\SimpleHal\Uri\LeagueUriJoiner;

$client = new GuzzleHalClient(new Client());
$uriTemplateProcessor = new GuzzleUriTemplateProcessor();
$uriJoiner = new LeagueUriJoiner();
$apiRootUrl = 'http://haltalk.herokuapp.com';

$root = new Resource($client, $uriTemplateProcessor, $uriJoiner, $apiRootUrl);

$user = $root->{'ht:me'}([ 'name' => 'clanie' ]);
echo "<br/>";
echo $root->{'ht:latest-posts'}->{'ht:post'}[0]->refresh()->content;


echo "<h1>{$user->real_name}'s Posts</h1><br/>";
$posts = $user->{'ht:posts'};

foreach($posts->{'ht:post'} as $post)
{
    echo $post->content . "<br/><br/>";
}