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
namespace Stormsys\SimpleHal\Exception;

use Exception;

/**
 * An exception which describes when a link relation was required for navigation but not found.
 *
 * @author Diogo Moura
 * @package Stormsys.SimpleHal
 */
class LinkNotPresentException extends \Exception {
    private $rel;

    public function __construct($rel, $code = 0, Exception $previous = null) {
        parent::__construct("{$rel} was not present in the resources _links.", $code, $previous);

        $this->rel = $rel;
    }

    /**
     * Gets the link relation key that was not present.
     *
     * @return string link relation key.
     */
    public function getRel()
    {
        return $this->rel;
    }

}