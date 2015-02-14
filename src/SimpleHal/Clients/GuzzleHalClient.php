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
namespace Stormsys\SimpleHal\Clients;

use Guzzle\Http\ClientInterface;

/**
 * Guzzle implementation of the HalClientInterface
 *
 * @author Diogo Moura
 * @package Stormsys.SimpleHal
 */
class GuzzleHalClient implements HalClientInterface
{
    private $client;

    public function __construct(ClientInterface $client) {
        $this->client = $client;
    }


    public function fromUrlAsJsonObject($url)
    {
        $response  = $this->client->get($url, ['Accept' => 'application/json'])->send();
        return json_decode($response->getBody(true), false);
    }
}
