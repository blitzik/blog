<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
 */

namespace Log;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="log_event")
 *
 */
class EventLog
{
    use Identifier;
    use MagicAccessors;

    const CREATION = 'creation';
    const REMOVAL = 'removal';
    const EDITING = 'editing';

    /**
     * @ORM\Column(name="name", type="string", length=50, nullable=false, unique=true)
     * @var string
     */
    protected $name;


    public function __construct($name)
    {
        $this->setName($name);
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    /**
     * @param string $name
     */
    private function setName($name)
    {
        Validators::assert($name, 'unicode:1..50');
        $this->name = $name;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    public function getName()
    {
        return $this->name;
    }

}