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
namespace Stormsys\SimpleHal\Exception;

use Exception;

/**
 * An exception which describes when a link relation was required for
 * navigation but not found.
 *
 * @author Diogo Moura <diogo@stormsys.net>
 */
class LinkNotPresentException extends Exception
{
    /**
     * The relation which this exception describes.
     *
     * @var string
     */
    private $_rel;

    /**
     * Constructs the exception.
     *
     * @param string $rel link relation that this exception represents.
     */
    public function __construct($rel)
    {
        parent::__construct("{$rel} was not present in the resources _links.");

        $this->_rel = $rel;
    }

    /**
     * Gets the link relation key that was not present.
     *
     * @return string link relation key.
     */
    public function getRel()
    {
        return $this->_rel;
    }

}