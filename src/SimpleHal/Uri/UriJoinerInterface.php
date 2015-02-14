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
namespace Stormsys\SimpleHal\Uri;

/**
 * Joins two uris or takes the latter.
 *
 * @author Diogo Moura
 * @package Stormsys.SimpleHal
 * @link http://tools.ietf.org/html/rfc6570
 */
interface UriJoinerInterface
{
    /**
     * Joins a candidate uri to a base url if it is relative.
     *
     * @param string $baseUrl the base url which SHOULD be absolute.
     * @param string $candidateUri the candidate uri which is either absolute of relative.
     * @return string the URL which is either the absolute candidate, or the candidate relative tro the base.
     */
    public function join($baseUrl, $candidateUri);
}