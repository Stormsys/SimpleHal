<?php
/**
 * This file is part of the Stormsys.SimpleHal library
 *
 * @category SimpleHal
 * @package  Stormsys.SimpleHal
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/Stormsys/SimpleHal
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
 * Represents a simple Hal Resource which provides functionality to navigate
 * a hal api.
 *
 * @author Diogo Moura <diogo@stormsys.net>
 */
class Resource
{
    /**
     * Source Json that makes up the representation.
     *
     * @var \stdClass
     */

    private $_json;


    /**
     * Registry of embedded resources.
     *
     * @var array of string => Resource|Resource[]
     */

    private $_embedded = [];


    /**
     * The underlying HalClient by which to make requests.
     *
     * @var HalClientInterface
     */

    private $_client;


    /**
     * The current resources base url, used for navigating through relative links.
     *
     * @var string
     */

    private $_url;

    /**
     * The implementation for processing uri templates.
     *
     * @var UriTemplateProcessorInterface
     */
    private $_uriTemplateProcessor;

    /**
     * The implementation for joining relative uris.
     *
     * @var UriJoinerInterface
     */
    private $_uriJoiner;

    /**
     * @param HalClientInterface $client a hal client to handle the transport layer
     * @param UriTemplateProcessorInterface $uriTemplateProcessor URI processor instance
     * @param UriJoinerInterface $uriJoiner a URI joiner instance.
     * @param $url
     * @param stdClass $json
     */
    public function __construct(
        HalClientInterface $client,
        UriTemplateProcessorInterface $uriTemplateProcessor,
        UriJoinerInterface $uriJoiner,
        $url, stdClass
        $json = null
    ) {
        $this->_client = $client;
        $this->_uriTemplateProcessor = $uriTemplateProcessor;
        $this->_uriJoiner = $uriJoiner;
        $this->_url = $url;

        // Either obtain the json from the HalClient or load it from
        // the json provided.
        if ($json == null) {
            $this->_json = $this->_client->fromUrlAsJsonObject($url);
        } else {
            $this->_json = $json;

            // for embedded resources update the url that will act as the base
            // url for this resource.
            $this->_url = $this->_parseLink($this->link('self'));
        }

        //wrap up all embedded resources.
        $this->_wrapEmbedding();
    }


    /**
     * Internally loads the $embedded property, wrapping the json in a new Resource.
     *
     * @return void
     */
    private function _wrapEmbedding()
    {

        //check if we have any embedded resources in the json.
        if (isset($this->_json->_embedded)) {

            foreach ($this->_json->_embedded as $key => $json) {

                $this->_embedded[$key] = [];

                // if the relation is just a resource, load only this otherwise load
                // an array or resources to match.
                if (is_array($json)) {

                    foreach ($json as $jsonChild) {

                        $this->_embedded[$key][] = new Resource(
                            $this->_client,
                            $this->_uriTemplateProcessor,
                            $this->_uriJoiner,
                            $this->_url,
                            $jsonChild
                        );

                    }

                } else {

                    $this->_embedded[$key][] = new Resource(
                        $this->_client,
                        $this->_uriTemplateProcessor,
                        $this->_uriJoiner,
                        $this->_url,
                        $json
                    );

                }

            }

        }

    }

    /**
     * Processes a json link and obtains an absolute url for navigation.
     *
     * @param \stdClass $link      a link
     * @param array     $variables variables to process the URI
     *
     * @return string a parsed URL.
     */
    private function _parseLink(stdClass $link, array $variables = [])
    {
        $uri = $link->href;

        if (isset($link->templated) && $link->templated) {

            $uri = $this->_uriTemplateProcessor->process($uri, $variables);
        }

        return $this->_uriJoiner->join($this->_url, $uri);
    }


    /**
     * Gets the Embedded Resource OR follows the link OR reads the resource property
     * which match the $name in that order.
     *
     * @param string $name      link relation or property name
     * @param array  $variables variables to process the URI
     *
     * @return null|Resource|mixed The Resource, property value or null if not found.
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
     * Gets a resource by its rel, by first checking the embedded items
     * and then following the link if it was not found.
     *
     * @param string $rel       link relation
     * @param array  $variables variables to process the URI
     *
     * @return null|Resource the Resource matching the rel, null if none found.
     */
    private function _getResourceFromRel($rel, array $variables = [])
    {
        if (isset($this->_embedded[$rel])) {
            return $this->_embedded[$rel];
        }

        if ($link = $this->link($rel) != null) {
            return $this->follow($rel, $variables);
        }

        return null;
    }

    /**
     * Gets a property from the underlying Hal json.
     *
     * @param string $name resource property name
     *
     * @return null|mixed the property value, if it does not exist then null.
     */
    private function _getProperty($name)
    {
        if (isset($this->_json->{$name})) {
            return $this->_json->{$name};
        }

        return null;
    }

    /**
     * Follows a link relation, resulting in a new Resource.
     *
     * @param string|stdClass $rel       the link relation or link
     * @param array           $variables variables to process the URI
     *
     * @return Resource Resource for given rel/link.
     *
     * @throws LinkNotPresentException relation is not part of the current resource.
     */
    public function follow($rel, array $variables = [])
    {
        $link = $rel instanceof stdClass ? $rel : $this->link($rel);

        if ($link != null) {

            return new Resource(
                $this->_client,
                $this->_uriTemplateProcessor,
                $this->_uriJoiner,
                $this->_parseLink($this->link($rel), $variables)
            );

        }

        throw new LinkNotPresentException($rel);
    }

    /**
     * Gets the first link for a given relation.
     *
     * @param string $rel link relation
     *
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
     * @param string $name the name of the underlying property
     *
     * @return null|mixed the property value
     */
    public function prop($name)
    {
        return $this->_getProperty($name);
    }

    /**
     * Gets a link or list of links for a given relation.
     *
     * @param string $rel the relation.
     *
     * @return null|stdClass|\stdClass[] a list of links where there are many for a
     *                                   given relation or a single link where
     *                                   there is a single relation or null where a
     *                                   link is not found.
     */
    public function links($rel)
    {
        if (isset($this->_json->_links->{$rel})) {
            return $this->_json->_links->{$rel};
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

        return new Resource(
            $this->_client,
            $this->_uriTemplateProcessor,
            $this->_uriJoiner,
            $this->_parseLink($this->link('self'))
        );

    }

    /**
     * Gets an embedded resource or resources by its relation.
     *
     * @param string $rel the relation.
     *
     * @return null|Resource|Resource[] Resource(s).
     */
    public function embedded($rel)
    {
        return isset($this->_embedded[$rel]) ? $this->_embedded[$rel] : null;
    }


    /**
     * Magic accessor will provide access to (in this order) an embedded resource
     * or resources by relation or a new resource obtained by following the link
     * for the given relation OR a property value.
     *
     * @example $resource->{'hal:embedded'};
     * @example $resource->{'hal:embedded'}[0];
     * @example $resource->{'hal:me'};
     * @example $resource->{'first_name'};
     *
     * @param string $name link relation or property name
     *
     * @return null|Resource|Resource[]|mixed Embedded Resource(s), Related Resource,
     *                                        Property or Null.
     */
    public function __get($name)
    {
        return $this->_getResourceOrProperty($name);
    }

    /**
     * Magic accessor will provide access to (in this order) an embedded resource
     * or resources by relation or a new resource obtained by following the link
     * for the given relation OR a property value in the event that the link relation
     * was chosen, the first argument is treated as template variables.
     *
     * @param string $name link relation or property name
     * @param array  $args an array where the first element is the $variables
     *                     for a URI template
     *
     * @example $resource->{'hal:embedded'}();
     * @example $resource->{'hal:users'}(['id' => 1]);
     * @example $resource->{'hal:me'}();
     * @example $resource->{'first_name'}();
     *
     * @return null|Resource|Resource[]|mixed Embedded Resource(s), Related Resource,
     *                                        Property or Null.
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

