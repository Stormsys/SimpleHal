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
 * Processes URI templates using an array of variables
 *
 * @author Diogo Moura <diogo@stormsys.net>
 * @link   http://tools.ietf.org/html/rfc6570
 */
interface UriTemplateProcessorInterface
{
    /**
     * Process the URI template using the supplied variables
     *
     * @param string $template  URI Template to expand
     * @param array  $variables Variables to use with the expansion
     *
     * @return string Returns the expanded template url
     */
    public function process($template, array $variables);
}