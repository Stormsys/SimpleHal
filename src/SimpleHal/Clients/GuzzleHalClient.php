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
namespace Stormsys\SimpleHal\Clients;

use GuzzleHttp\ClientInterface;

/**
 * Guzzle implementation of the HalClientInterface
 *
 * @author Diogo Moura <diogo@stormsys.net>
 */
class GuzzleHalClient implements HalClientInterface
{
    private $_client;

    /**
     * Constructs the class.
     *
     * @param ClientInterface $client a Guzzle Client implementation.
     */
    public function __construct(ClientInterface $client)
    {
        $this->_client = $client;
    }

    /**
     * Gets the hal resource for a given url and deserialize's this into stdClass.
     *
     * @param string $url url of the resource.
     *
     * @return \stdClass the php json object.
     */
    public function fromUrlAsJsonObject($url)
    {
        $headers = ['Accept' => 'application/json'];
        $response  = $this->_client->get($url, [ 'headers' => $headers ]);
        return json_decode($response->getBody(true), false);
    }
}
