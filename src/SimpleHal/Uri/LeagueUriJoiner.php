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

use League\Url\Components\Path;
use League\Url\Url;

/**
 * League implementation for the UriJoinerInterface
 *
 * @author Diogo Moura
 * @package Stormsys.SimpleHal
 */
class LeagueUriJoiner implements UriJoinerInterface {
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
                        unset($currentUrl->getPath()[count($currentUrl->getPath()) - 1]);
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