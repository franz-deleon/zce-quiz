<?php
namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_answers")
 */
class Answers extends EntityAbstract
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="instance_id")
     */
    protected $instanceId;

    /**
     * @ORM\Column(type="integer", name="question_id")
     */
    protected $questionId;

    /**
     * @ORM\Column(type="integer", name="q_order")
     */
    protected $order;

    /**
     * @ORM\Column(type="text")
     */
    protected $answer;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('y', 'n')")
     */
    protected $correct;
}