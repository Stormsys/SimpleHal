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

use League\Url\Components\Path;
use League\Url\Url;

/**
 * League implementation for the UriJoinerInterface
 *
 * @author Diogo Moura <diogo@stormsys.net>
 */
class LeagueUriJoiner implements UriJoinerInterface
{
    /**
     * Joins a candidate uri to a base url if it is relative.
     *
     * @param string $baseUrl      the base url which SHOULD be absolute.
     * @param string $candidateUri a candidate relative or absolute URI.
     *
     * @return string               Absolute URL
     */
    public function join($baseUrl, $candidateUri)
    {
        if (preg_match('/:\/\//', $candidateUri) === 0) {
            $currentUrl = Url::createFromUrl($baseUrl);
            $pathParts = $output = preg_split("/(\\\\|\/)/", $candidateUri);
            $resetPath = (preg_match('/^(\/|\\\\)/', $candidateUri) === 1);

            if ($resetPath) {
                $currentUrl->setPath(new Path());
            }

            foreach ($pathParts as $pathPart) {
                switch ($pathPart) {
                case '..':
                    $lastElementPos = count($currentUrl->getPath()) - 1;
                    unset($currentUrl->getPath()[$lastElementPos]);
                    break;
                case '':
                case '.':
                    break;
                default:
                    $currentUrl->getPath()->append($pathPart);
                    break;
                }
            }

            return (string)$currentUrl;
        }

        return $candidateUri;
    }
}