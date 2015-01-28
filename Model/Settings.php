<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Model;

class Settings
{
    protected $positions;

    /**
     * Gets the value of positions.
     *
     * @return mixed
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * Sets the value of positions.
     *
     * @param mixed $positions the positions
     *
     * @return self
     */
    public function setPositions($positions)
    {
        $this->positions = $positions;

        return $this;
    }
}
