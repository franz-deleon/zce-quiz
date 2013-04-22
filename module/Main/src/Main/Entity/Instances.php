<?php
namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_instances")
 */
class Instances extends EntityAbstract
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", name="at_question")
     */
    protected $atQuestion;

    /**
     * @ORM\Column(type="datetime", name="time_start")
     */
    protected $timeStart;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('y', 'n')")
     */
    protected $finished;

    /**
     * @ORM\Column(type="integer")
     */
    protected $score;
}