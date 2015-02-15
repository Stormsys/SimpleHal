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

/**
 * PHP file_get_contents Implementation of the HalClientInterface
 *
 * @author Diogo Moura <diogo@stormsys.net>
 */
class FileGetContentsHalClient implements HalClientInterface
{
    /**
     * Gets the hal resource for a given url and deserialize's this into stdClass.
     *
     * @param string $url url of the resource.
     *
     * @return \stdClass the php json object.
     */
    public function fromUrlAsJsonObject($url)
    {
        $contextSettings = ['http' => [ 'header' => 'Accept: application/json']];
        $context = stream_context_create($contextSettings);

        return json_decode(file_get_contents($url, false, $context));
    }
}