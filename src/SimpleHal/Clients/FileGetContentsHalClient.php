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

use string;
/**
 * PHP file_get_contents Implementation of the HalClientInterface
 *
 * @author Diogo Moura
 * @package Stormsys.SimpleHal
 */
class HalClient implements HalClientInterface
{
    /**
     * {@inherit}
     */
    public function fromUrlAsJsonObject($url)
    {
        $context = stream_context_create(array('http' => array(
            'header' => 'Accept: application/json'
        )));

        return json_decode(file_get_contents($url, false, $context));
    }
}