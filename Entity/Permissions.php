<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Newscoop\Entity\User;

/**
 * Users permissions entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_editor_bundle_permissions")
 */
class Permissions
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="Id")
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(type="boolean", name="is_assigned")
     * @var boolean
     */
    protected $isAssigned;

    /**
     * @ORM\Column(type="datetime", name="updated_at")
     * @var datetime
     */
    protected $updatedAt;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setUpdatedAt(new \DateTime());
        $this->setIsAssigned(true);
    }

    /**
     * Gets the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param int $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the value of user.
     *
     * @param User $user the user
     *
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the value of isAssigned.
     *
     * @return boolean
     */
    public function getIsAssigned()
    {
        return $this->isAssigned;
    }

    /**
     * Sets the value of isAssigned.
     *
     * @param boolean $isAssigned the is assigned
     *
     * @return self
     */
    public function setIsAssigned($isAssigned)
    {
        $this->isAssigned = $isAssigned;

        return $this;
    }

    /**
     * Gets the value of updatedAt.
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the value of updatedAt.
     *
     * @param datetime $updatedAt the updated at
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
