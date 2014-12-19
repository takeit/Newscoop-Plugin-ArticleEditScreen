<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Model;

class Position
{
    public $titlePosition;

    public $articleTypeName;

    /**
     * Gets the value of articleTypeName.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->articleTypeName;
    }

    /**
     * Sets the value of articleTypeName.
     *
     * @param mixed $articleTypeName the article type name
     *
     * @return self
     */
    public function setName($articleTypeName)
    {
        $this->articleTypeName = $articleTypeName;

        return $this;
    }

    /**
     * Gets the value of position.
     *
     * @return mixed
     */
    public function getPosition()
    {
        return $this->titlePosition;
    }

    /**
     * Sets the value of position.
     *
     * @param mixed $titlePosition the position
     *
     * @return self
     */
    public function setPosition($titlePosition)
    {
        $this->titlePosition = $titlePosition;

        return $this;
    }
}
