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

use Guzzle\Parser\UriTemplate\UriTemplate;

/**
 * Guzzle implementation for the UriTemplateProcessor

 * @author Diogo Moura
 * @package Stormsys.SimpleHal
 */
class GuzzleUriTemplateProcessor implements UriTemplateProcessorInterface
{
    /**
     * The Guzzle URI Template underlying the class.
     *
     * @var UriTemplate
     */
    private $processor;

    public function __construct() {
        $this->processor = new UriTemplate();
    }

    public function process($template, array $variables) {
        return $this->processor->expand($template, $variables);
    }
}