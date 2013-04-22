<?php
namespace Main\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Main\Model\ExamManager;
use Main\Entity\Questions;
use Main\Entity\Answers;
use Main\Entity\Instances;
use Main\Forms;

class IndexController extends AbstractActionController
{
    protected $_examManager;

    public function indexAction()
    {
        $this->_examManager = $this->getServiceLocator()->get('ExamManager');

        if ($this->_examManager->isExamFinished()) {
            return $this->forward()->dispatch('indexController', array('action' => 'result'));
        }

        if ($this->_examManager->isInstanceExpired()) {
            return $this->forward()->dispatch('indexController', array('action' => 'exam-ended'));
        }

        if ($this->getRequest()->isPost()) {
            // do the updating of the question
            $user = $this->getRequest()->getPost();
            $question = $this->_examManager->getCurrentQuestion();
            $instance = $this->_examManager->getInstance();

            $rightAnswer = ($question->type == 'multi-correct') ? $question->multRightAnswer : $question->singleRightAnswer;
            $rightAnswer = explode("|", $rightAnswer);

            $userAnswer = (is_array($user->answer)) ? implode("|", $user->answer) : $user->answer;

            // figure if the user's answer is correct or not
            if (is_array($user->answer)) {
                $answer = true;
                foreach ($user->answer as $answer) {
                    if (!in_array($answer, $rightAnswer)) {
                        $answer = false;
                        break;
                    }
                }
            } else {
                $answer = true;
                if (!in_array($user->answer, $rightAnswer)) {
                    $answer = false;
                }
            }

            $correct = 0;
            $finished = ((ExamManager::QUESTION_COUNT) <= $instance->atQuestion) ? true : false;
            if ($finished) {
                $scores = $this->_examManager->getScore();

                // count correct answers
                foreach ($scores as $score) {
                    if ($score['correct'] == 'y') {
                        $correct++;
                    }
                }
                // include the last question here
                if ($answer === true) {
                    $correct++;
                }
            }

            $this->_examManager->updateInstance(
                ($instance->atQuestion + 1),
                ($finished == true) ? 'y' : 'n',
                $correct
            );

            $this->_examManager->updateAnswer(
                ($instance->atQuestion - 1), //make sure to increment
                ($userAnswer),
                ($answer === true) ? 'y' : 'n'
            );

            if ($this->_examManager->isExamFinished()) {
                return $this->forward()->dispatch('indexController', array('action' => 'result'));
            }
        }

        $question = $this->_examManager->getCurrentQuestion();
        $instance = $this->_examManager->getInstance();

        $choices = array();
        if ($question->type != 'fill-in') {
            $choices = explode("|", $question->multiPossibleAnswers);
        }

        $counter = strtotime($this->_examManager->getTimeExpires()->format('Y-m-d h:i:s')) - time();

        return new ViewModel(array(
            'questionNo' => $instance->atQuestion,
            'timeRemaining' => date('i:s', $counter),
        	'questionType' => $question->type,
            'question' => $question->question,
            'choices' => $choices,
        ));
    }

    public function questionAction()
    {
        print "question";
        return new ViewModel();
    }

    public function resultAction()
    {
        $this->_examManager = $this->getServiceLocator()->get('ExamManager');
        $correct = 0;
        $scores = $this->_examManager->getScore();

        // count correct answers
        foreach ($scores as $score) {
            if ($score['correct'] == 'y') {
                $correct++;
            }
        }
        // include the last question here
        if ($answer === true) {
            $correct++;
        }

        $passed = false;
        $passing = round(ExamManager::QUESTION_COUNT * .7);
        if ($correct >= $passing) {
            $passed = true;
        }

        return new ViewModel(array(
            'passed' => $passed,
        ));
    }

    public function addQuestionAction()
    {
        return new ViewModel();
    }

    public function editQuestionAction()
    {
        return new ViewModel();
    }

    public function examEndedAction()
    {
        return new ViewModel();
    }

    public function renewAction()
    {
        $this->_examManager = $this->getServiceLocator()->get('ExamManager');
        $this->_examManager->renewInstance();
        $this->redirect()->toRoute('home');
    }
}