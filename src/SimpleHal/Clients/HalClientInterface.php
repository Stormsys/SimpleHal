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
 * Interface that allows for custom implementations of the http navigation layer.
 *
 * @author Diogo Moura <diogo@stormsys.net>
 */
interface HalClientInterface
{
    /**
     * Gets the hal resource for a given url and deserialize's this into stdClass.
     *
     * @param string $url url of the resource.
     *
     * @return \stdClass the php json object.
     */
    function fromUrlAsJsonObject($url);
}