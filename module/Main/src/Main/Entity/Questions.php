<?php
namespace Main\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="questions")
 */
class Questions extends EntityAbstract
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(type="text")
     */
    protected $question;

    /**
     * @ORM\Column(type="text", name="multi_possible_answers", nullable=true)
     */
    protected $multiPossibleAnswers;

    /**
     * @ORM\Column(type="string", name="multi_right_answer", nullable=true)
     */
    protected $multiRightAnswer;

    /**
     * @ORM\Column(type="string", name="single_right_answer", nullable=true)
     */
    protected $singleRightAnswer;

    /**
     * @ORM\Column(type="string")
     */
    protected $level;

    /**
     * @ORM\Column(type="text")
     */
    protected $explanation;
}