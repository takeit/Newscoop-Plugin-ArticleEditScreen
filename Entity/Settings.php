<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Newscoop\Entity\User;
use Newscoop\EditorBundle\Model\Settings as BaseSettings;

/**
 * Settigns entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_editor_bundle_settings")
 */
class Settings extends BaseSettings
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, name="varname")
     * @var string
     */
    protected $option;

    /**
     * @ORM\Column(type="text", name="value", nullable=true)
     * @var text
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="Id", nullable=true)
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var datetime
     */
    protected $created;

    /**
     * @ORM\Column(type="boolean", name="is_global")
     * @var boolean
     */
    protected $isGlobal;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->positions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setIsGlobal(false);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param  string $value
     * @return string
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set option
     *
     * @param  string $option
     * @return string
     */
    public function setOption($option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Set create date
     *
     * @param  datetime $created
     * @return datetime
     */
    public function setCreatedAt(\DateTime $created)
    {
        $this->created = $created;

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
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the value of isGlobal.
     *
     * @return boolean
     */
    public function getIsGlobal()
    {
        return $this->isGlobal;
    }

    /**
     * Sets the value of isGlobal.
     *
     * @param boolean $isGlobal the is global
     *
     * @return self
     */
    public function setIsGlobal($isGlobal)
    {
        $this->isGlobal = $isGlobal;

        return $this;
    }
}
