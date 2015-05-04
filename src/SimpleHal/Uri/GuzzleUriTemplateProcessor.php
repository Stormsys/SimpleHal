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

use GuzzleHttp\UriTemplate;

/**
 * Guzzle implementation for the UriTemplateProcessor
 *
 * @author Diogo Moura <diogo@stormsys.net>
 */
class GuzzleUriTemplateProcessor implements UriTemplateProcessorInterface
{
    /**
     * The Guzzle URI Template underlying the class.
     *
     * @var UriTemplate
     */
    private $_processor;

    /**
     * Creates a new GuzzleUriTemplateProcessor
     */
    public function __construct()
    {
        $this->_processor = new UriTemplate();
    }

    /**
     * Process the URI template using the supplied variables
     *
     * @param string $template  URI Template to expand
     * @param array  $variables Variables to use with the expansion
     *
     * @return string Returns the expanded template url
     */
    public function process($template, array $variables)
    {
        return $this->_processor->expand($template, $variables);
    }
}