<?php
/**
 * This file is part of the Stormsys.SimpleHal library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/Stormsys/SimpleHal
 * @package Stormsys.SimpleHal
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Stormsys\SimpleHal;

use stdClass;
use Stormsys\SimpleHal\Clients\HalClientInterface;
use Stormsys\SimpleHal\Exception\LinkNotPresentException;
use Stormsys\SimpleHal\Uri\UriJoinerInterface;
use Stormsys\SimpleHal\Uri\UriTemplateProcessorInterface;


/**
 * Represents a simple Hal Resource which provides functionality to navigate a hal api.
 *
 * @author Diogo Moura
 * @package Stormsys.SimpleHal
 */
class Resource
{
    /**
     * Source Json that makes up the representation.
     *
     * @var \stdClass
     */

    private $json;


    /**
     * Registry of embedded resources.
     *
     * @var array of string => Resource|Resource[]
     */

    private $embedded = [];


    /**
     * The underlying HalClient by which to make requests.
     *
     * @var HalClientInterface
     */

    private $client;


    /**
     * The current resources base url, used for navigating through relative links.
     *
     * @var string
     */

    private $url;

    /**
     * The implementation for processing uri templates.
     *
     * @var UriTemplateProcessorInterface
     */
    private $uriTemplateProcessor;

    /**
     * The implementation for joining relative uris.
     *
     * @var UriJoinerInterface
     */
    private $uriJoiner;


    public function __construct(HalClientInterface $client, UriTemplateProcessorInterface $uriTemplateProcessor, UriJoinerInterface $uriJoiner, $url, stdClass $json = null)
    {
        $this->client = $client;
        $this->uriTemplateProcessor = $uriTemplateProcessor;
        $this->uriJoiner = $uriJoiner;
        $this->url = $url;

        //Either obtain the json from the HalClient or load it from the json provided.
        if ($json == null) {
            $this->json = $this->client->fromUrlAsJsonObject($url);
        } else {
            $this->json = $json;

            //for embedded resources update the url that will act as the base url for this resource.
            $this->url = $this->_parseLink($this->link('self'));
        }

        //wrap up all embedded resources.
        $this->_wrapEmbedding();
    }


    /**
     * internally loads the $embedded property, wrapping the json in a new Resource.
     */
    private function _wrapEmbedding()
    {
        //check if we have any embedded resources in the json.
        if (isset($this->json->_embedded)) {

            foreach ($this->json->_embedded as $key => $json) {
                $this->embedded[$key] = [];

                //if the relation is just a resource, load only this otherwise load an array or resources to match.
                if (is_array($json)) {
                    foreach ($json as $jsonChild) {
                        $this->embedded[$key][] = new Resource($this->client, $this->uriTemplateProcessor, $this->uriJoiner, $this->url, $jsonChild);
                    }
                } else {
                    $this->embedded[$key][] = new Resource($this->client, $this->uriTemplateProcessor, $this->uriJoiner, $this->url, $json);
                }

            }

        }
    }

    /**
     * Processes a json link and obtains an absolute url for navigation.
     *
     * @param  \stdClass $link internal use only, the json representation of the link.
     * @param  array $variables key value pairs to be processed by the RFC 6570 URI Template processor.
     * @return string                    a absolute uri, that has been processed for any template variables.
     */
    private function _parseLink(stdClass $link, array $variables = [])
    {
        $uri = $link->href;

        if (isset($link->templated) && $link->templated) {

            $uri = $this->uriTemplateProcessor->process($uri, $variables);
        }

        return $this->uriJoiner->join($this->url, $uri);
    }


    /**
     * Gets the first Embedded Resource, new Resource via Link or Json Property to match the $name in that order.
     *
     * @param string $name the link relation, embeded link relation or property name.
     * @param array $variables in the event that we are following a link, process any template variables.
     * @return null|Resource|mixed The resource, property or null if not found.
     */
    private function _getResourceOrProperty($name, array $variables = [])
    {
        $result = $this->_getResourceFromRel($name, $variables);

        if ($result != null) {
            return $result;
        }

        return $this->_getProperty($name);
    }

    /**
     * Gets a resource by its rel, by first checking the embedded items and then following the link if it was not found.
     *
     * @param string $rel link relation
     * @param array $variables variables to process if following a link.
     * @return null|Resource the Resource matching the rel, null if none found.
     */
    private function _getResourceFromRel($rel, array $variables = [])
    {
        if (isset($this->embedded[$rel])) {
            return $this->embedded[$rel];
        }

        if ($link = $this->link($rel) != null) {
            return $this->follow($rel, $variables);
        }

        return null;
    }

    /**
     * Gets a property from the underlying Hal json.
     *
     * @param string $name the name of the property
     * @return null|mixed the property value, if it does not exist then null.
     */
    private function _getProperty($name)
    {
        if (isset($this->json->{$name})) {
            return $this->json->{$name};
        }

        return null;
    }

    /**
     * Follows a link relation, resulting in a new Resource with that links representation.
     *
     * @param string $rel the link relation.
     * @param array $variables key value pairs in event of any template uris.
     * @return Resource the new representation.
     * @throws LinkNotPresentException when the relation is not part of the current resource.
     */
    public function follow($rel, array $variables = [])
    {
        $link = $this->link($rel);

        if ($link != null) {
            return new Resource($this->client, $this->uriTemplateProcessor, $this->uriJoiner, $this->_parseLink($this->link($rel), $variables), $this->config);
        }

        throw new LinkNotPresentException($rel);
    }

    /**
     * Gets the first link for a given relation.
     *
     * @param string $rel the relation.
     * @return null|stdClass first link to match the relation or null.
     */
    public function link($rel)
    {
        $links = $this->links($rel);

        if (is_array($links)) {
            return $links[0];
        }

        return $links;
    }

    /**
     * Get a property by its name from the underlying json.
     *
     * @param string $name the name of the underlying property.
     * @return null|mixed if the property exists then it is returned else null is returned.
     */
    public function prop($name)
    {
        return $this->_getProperty($name);
    }

    /**
     * Gets a link or list of links for a given relation.
     *
     * @param string $rel the relation.
     * @return null|stdClass|\stdClass[] a list of links where there are many for a given relation
     *                                     or a single link where there is a single relation
     *                                     or null where a link is not found.
     */
    public function links($rel)
    {
        if (isset($this->json->_links->{$rel})) {
            return $this->json->_links->{$rel};
        }

        return null;
    }

    /**
     * Embedded Resources are sometimes partially or inconsistently represented,
     * this will obtain the full representation by following the self link
     * (this method is syntax sugar for ->refresh()).
     *
     * @return Resource
     */
    public function full()
    {
        return $this->refresh();
    }

    /**
     * Convenience method for reloading the current resource via its self link.
     *
     * @return Resource
     */
    public function refresh()
    {
        return new Resource($this->client, $this->uriTemplateProcessor, $this->uriJoiner, $this->_parseLink($this->link('self')), $this->config);
    }

    /**
     * Gets an embedded resource or resources by its relation.
     *
     * @param string $rel the relation.
     * @return null|Resource|Resource[] the resource, resource's or null where it was not found.
     */
    public function embedded($rel)
    {
        return isset($this->embedded[$rel]) ? $this->embedded[$rel] : null;
    }


    /**
     * Magic accessor will provide access to (in this order) an embedded resource or resources by relation OR
     *  a new resource obtained by following the link for the given relation OR a property value.
     *
     * @example $resource->{'hal:embedded'};
     * @example $resource->{'hal:embedded'}[0];
     * @example $resource->{'hal:me'};
     * @example $resource->{'first_name'};
     *
     * @param $name
     * @return null|Resource|Resource[]|mixed Embedded Resource(s), Related Resource, Property or Null.
     */
    public function __get($name)
    {
        return $this->_getResourceOrProperty($name);
    }

    /**
     * Magic accessor will provide access to (in this order) an embedded resource or resources by relation OR
     *  a new resource obtained by following the link for the given relation OR a property value.
     *
     * in the event that the link relation was chosen, the first argument is treated as template variables.
     *
     * @example $resource->{'hal:embedded'}();
     * @example $resource->{'hal:users'}(['id' => 1]);
     * @example $resource->{'hal:me'}();
     * @example $resource->{'first_name'}();
     *
     * @param string $name
     * @return null|Resource|Resource[]|mixed Embedded Resource(s), Related Resource, Property or Null.
     */
    public function __call($name, $args)
    {
        $variables = [];

        if (is_array($args[0])) {
            $variables = $args[0];
        }

        return $this->_getResourceOrProperty($name, $variables);
    }
}

