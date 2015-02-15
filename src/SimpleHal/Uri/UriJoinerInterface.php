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
namespace Stormsys\SimpleHal\Uri;

/**
 * Joins two uris or takes the latter.
 *
 * @author Diogo Moura <diogo@stormsys.net>
 * @link   http://tools.ietf.org/html/rfc6570
 */
interface UriJoinerInterface
{
    /**
     * Joins a candidate uri to a base url if it is relative.
     *
     * @param string $baseUrl      the base url which SHOULD be absolute.
     * @param string $candidateUri a candidate relative or absolute URI.
     *
     * @return string               Absolute URL
     */
    public function join($baseUrl, $candidateUri);
}