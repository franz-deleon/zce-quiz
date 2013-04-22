<?php
namespace Main\Model;

use Zend\Http\Header\SetCookie,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\QueryBuilder,
    Main\Entity\Instances,
    Main\Entity\Answers;


class ExamManager
{
    const COOKIE_NAME = 'exam_instance';

    const SECONDS_PER_QUESTION = 50;

    const QUESTION_COUNT = 10;

    protected $_answer;

    protected $_em;

    protected $_eventManager;

    protected $_cookieId;

    protected $_questions;

    /**
     * The exam instance
     * @var Main\Entity\Instances
     */
    protected $_instance;

    /**
     * Set the entity manager
     * @param unknown_type $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->_em = $em;
        $this->_em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Try to retrieve a cookie id if not create it.
     *
     * @param void
     * @return string The cookie id
     */
    public function getCookieId()
    {
        if (null === $this->_cookieId) {
            $cookie = $_COOKIE[self::COOKIE_NAME];
            if (!empty($cookie)) {
                $this->_cookieId = $cookie;
            } else {
                $cookieVal = uniqid('ei-');
                setrawcookie(self::COOKIE_NAME, $cookieVal, 0, '/');
                $this->_cookieId = $cookieVal;
            }
        }

        return $this->_cookieId;
    }

    public function getInstance($forceRenew = false)
    {
        if (null === $this->_instance || $forceRenew == true) {
            $instanceId = $this->getCookieId();
            $this->_instance = $this->_em->find('Main\Entity\Instances', $instanceId);
            if (null === $this->_instance) {
                $this->createInstance();
                $this->_instance = $this->_em->find('Main\Entity\Instances', $instanceId);
            }
            $this->_em->persist($this->_instance);
            $this->_em->flush();
        }
        return $this->_instance;
    }

    public function setInstance($instance)
    {
        $this->_instance = $instance;
    }


    /**
     * retrieve a single question
     */
    public function getQuestion($id, $order = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('q')
           ->from('Main\Entity\Questions', 'q')
           ->where('q.id = ?1')
           ->setParameter(1, $id);
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * retrieve all questions
     *
     * @return array
     */
    public function getQuestions()
    {
        if (null === $this->_questions) {
            $qb = $this->_em->createQueryBuilder();
            $qb->select('q')
               ->from('Main\Entity\Questions', 'q');
            $this->_questions = $qb->getQuery()->getArrayResult();
        }
        return $this->_questions;
    }

    /**
     * Retrieve question that have not been used yet
     * @return array
     */
    public function getUnusedQuestions()
    {
        $instance = $this->getInstance();

        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
           ->from('Main\Entity\Answers', 'a')
           ->where('a.instanceId = ?1')
           ->setParameter(1, $instance->id);
        $usedQuestions = $qb->getQuery()->getArrayResult();

        if (!empty($usedQuestions)) {
            $temp = array();
            foreach ($usedQuestions as $question) {
                $temp[] = $question['questionId'];
            }
        }

        $qb = $this->_em->createQueryBuilder();
        if (empty($temp)) {
           $qb->select('q')->from('Main\Entity\Questions', 'q');
        } else {
           $qb->select('q')
              ->from('Main\Entity\Questions', 'q')
              ->where($qb->expr()->notIn('q.id', $temp));
        }

        // retrieve question data if any
        $safeQuestions = $qb->getQuery()->getResult();

        return $safeQuestions;
    }

    public function createInstance()
    {
        $newInstance = new Instances;
        $newInstance->id = $this->getCookieId();
        $newInstance->atQuestion = 1;
        $newInstance->timeStart = new \DateTime('now');
        $newInstance->finished = 'n';
        $newInstance->score = 0;

        $this->_em->persist($newInstance);
        $this->_em->flush();
    }

    public function isExamFinished()
    {
        $instance = $this->getInstance(true); // will force to renew the instance
        if ($instance->finished == 'y') {
            return true;
        }
        return false;
    }

    public function getTimeExpires()
    {
        $ui = $this->getInstance();

        // calculate total exam time
        $maxTime = self::QUESTION_COUNT * self::SECONDS_PER_QUESTION;
        $timeEnds = new \DateTime($ui->timeStart->format("Y/m/d h:i:s"));
        $timeEnds->modify("+{$maxTime} seconds");//

        return $timeEnds;
    }

    public function isInstanceExpired()
    {
        $now = new \DateTime('now');
        if ($now->format('ymdhis') > $this->getTimeExpires()->format('ymdhis')) {
            return true;
        }
        return false;
    }

    /**
     * retrieve question data
     *
     * @param void
     * @return array
     */
    public function getCurrentQuestion()
    {
        $ui = $this->getInstance();

        // search for an instance of the question number
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
           ->from('Main\Entity\Answers', 'a')
           ->where($qb->expr()->andX(
               $qb->expr()->eq('a.instanceId', '?1'),
               $qb->expr()->eq('a.order', '?2')
           ))
           ->setParameters(array(1 => $ui->id, 2 => $ui->atQuestion));

        // retrieve question data if any
        $answer = $qb->getQuery()->getResult();

        // question is not picked yet so randomly pick from the question pool
        if (empty($answer)) {
            $questionData = $this->getUnusedQuestions();
            $randomKey = array_rand($questionData);
            // save the retrieved question
            $questionId = $questionData[$randomKey]->id;
            $this->createAnswerInstance($questionId);
        } else {
            $questionId = $answer[0]->questionId;
        }

        return $this->getQuestion($questionId);
    }

    public function createAnswerInstance($questionId, $order = '', $answer = '', $correct = '')
    {
        $instance = $this->getInstance();

        $this->_answer = new Answers;
        $this->_answer->instanceId = $instance->id;
        $this->_answer->questionId = $questionId;
        $this->_answer->order = !empty($order) ? $order : $instance->atQuestion;
        $this->_answer->answer = !empty($answer) ? $answer : null;
        $this->_answer->correct = !empty($correct) ? $correct : null;

        $this->_em->persist($this->_answer);
        $this->_em->flush();
    }

    public function getAnswer($order = null)
    {
        $instance = $this->getInstance();
        $order = isset($order) ? $order : $instance->atQuestion;

        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
           ->from('Main\Entity\Answers', 'a')
           ->where($qb->expr()->andX(
               $qb->expr()->eq('a.instanceId', '?1'),
               $qb->expr()->eq('a.order', '?2')
           ))
           ->setParameters(array(1 => $instance->id, 2 => $order));
        $answer = $qb->getQuery()->getResult();

        return $answer;
    }

    public function getAnswerInstance($order = null)
    {
        return $this->_answer;
    }

    public function updateAnswer($order = '', $answer = '', $correct = '')
    {
        $instance = $this->getInstance();
        $ans = $this->getAnswer($order);

        $qb = $this->_em->createQueryBuilder();
        $q = $qb->update('Main\Entity\Answers', 'a')
                ->set('a.answer', $qb->expr()->literal($answer))
                ->set('a.correct', $qb->expr()->literal($correct))
                ->where($qb->expr()->andX(
                    $qb->expr()->eq('a.instanceId', '?1'),
                    $qb->expr()->eq('a.order', '?2')
                ))
                ->setParameters(array(1 => $instance->id, 2 => $ans[0]->order))
                ->getQuery();
        $p = $q->execute();
        $this->_em->flush();
    }

    public function renewInstance()
    {
        setrawcookie(self::COOKIE_NAME, '', 1, '/');
        $this->setInstance(null);
    }

    public function updateInstance($atQuestion = '', $finished = 'n', $score = 0)
    {
        $instance = $this->getInstance();

        $qb = $this->_em->createQueryBuilder();
        $q = $qb->update('Main\Entity\Instances', 'i')
                ->set('i.atQuestion', $qb->expr()->literal($atQuestion))
                ->set('i.finished', $qb->expr()->literal($finished))
                ->set('i.score', $qb->expr()->literal($score))
                ->where('i.id = ?1')
                ->setParameters(array(1 => $instance->id))
                ->getQuery();

        $instance->atQuestion = $atQuestion;
        $instance->finished = $finished;
        $instance->score = $score;

        $this->_em->persist($instance);
        $this->_em->flush();

        $p = $q->execute();
    }

    public function getScore()
    {
        $instance = $this->getInstance();
        $qb = $this->_em->createQueryBuilder();
        $q = $qb->select('a')
                ->from('Main\Entity\Answers', 'a')
                ->where('a.instanceId = ?1')
                ->setParameters(array(1 => $instance->id))
                ->getQuery();
        $answers = $q->getArrayResult();
        return $answers;
    }
}