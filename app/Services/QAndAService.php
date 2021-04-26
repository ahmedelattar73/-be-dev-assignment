<?php


namespace App\Services;


use App\Console\Commands\QAndA;
use App\Enums\AnswerOptionsEnum;
use App\Enums\QAOptionsEnum;
use App\Repositories\AnswerRepository;
use App\Repositories\QuestionRepository;
use Illuminate\Database\Eloquent\Collection;

class QAndAService
{

    /**
     * The console object.
     *
     * @var QAndA
     */
    private $console;

    /**
     * Selected option from the main options list.
     *
     * @var int
     */
    private $selectedOption;

    /**
     * Repository to deal with questions query.
     *
     * @var QuestionRepository
     */
    private $questionRepository;

    /**
     * Repository to deal with answers query.
     *
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * QAndAService constructor.
     * @param QuestionRepository $questionRepository
     * @param AnswerRepository $answerRepository
     */
    public function __construct(QuestionRepository $questionRepository, AnswerRepository $answerRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
    }

    /**
     * The entry point that set the console object and start show the user options.
     *
     * @param  QAndA $console
     * @return void
     */
    public function excute(QAndA $console) : void
    {
        $this->console = $console;
        $this->showOptions();
    }

    /**
     * Show available options list to the user
     *
     * @return void
     */
    private function showOptions() : void
    {
        $selectedOption = $this->console->choice(__('Please cheoose your option:'), $this->getAllOptions() );
        $this->setSelectedOption($selectedOption);
        $this->handleOption();
    }

    /**
     * Set the user selected option from the options list
     *
     * @param  int $selectedOption
     * @return void
     */
    private function setSelectedOption($selectedOption) : void
    {
        $this->selectedOption = $selectedOption ? array_search($selectedOption, $this->getAllOptions()) : null;
    }

    /**
     * Start to handle the logic of the user selected option
     *
     * @return void
     */
    private function handleOption() : void
    {
        switch ($this->selectedOption) {
            case QAOptionsEnum::ADD:
                $this->addQuestion();
            break;

            case QAOptionsEnum::VIEW:
                $this->viewQuestion();
                break;

            case QAOptionsEnum::SHOW_ANSWERS:
                $this->showAnswers();
                break;

            case QAOptionsEnum::QA_EXIT:
                $this->exit();
                break;
        }

        $this->showOptions($this->console);
    }

    /**
     * Add anew question and it's valid answer
     *
     * @return void
     */
    private function addQuestion() : void
    {
        $question = $this->console->ask(__('Type your question'));

        $this->console->info('Question successfully added ');

        $answer = $this->console->ask(__('Type your answer'));

        $this->console->info('Answer successfully added ');

        $this->questionRepository->create([
            'question'      => $question,
            'valid_answer'  => $answer,
        ]);
    }

    /**
     * View all added questions and the user progress with each question
     *
     * @return void
     */
    private function viewQuestion() : void
    {
        $allQuestions = $this->questionRepository->list(['id', 'question']);

        if( $this->questionRepository->hasUnAnsweredQuestions() ) {

            $this->transformQuestionsList($allQuestions);
            $this->chooseQuestionToAnswer();

        } elseIf(!$allQuestions->count()) {

            $this->console->info( 'No questions added. Add new question to start answering');

        } else {
            $this->showProgress();
        }
    }

    /**
     * View all user's answers.
     *
     * @return void
     */
    private function showAnswers() : void
    {
        $this->showProgress();
    }

    /**
     * Transform the questions list to add the user progress with each question
     *
     * @param Collection $allQuestions
     * @return void
     */
    private function transformQuestionsList($allQuestions) : void
    {
        $this->options = [];
        foreach ($allQuestions as $question) {
            if($question->answer) {
                $validate = $question->answer->is_true ? __('True') :  __('False');
                $this->options[$question->id] =  $question->question . ' ('.$validate .')';
            } else {
                $this->options[$question->id] =  $question->question ;
            }
        }
    }

    /**
     * Transform the questions list to add the user progress with each question
     *
     * @param Collection $allQuestions
     * @return void
     */
    private function transformProgressList($allQuestions) : void
    {
        $this->progress = [];
        foreach ($allQuestions as $question) {
            $this->progress[$question->id] = [
                'question'  => $question->question,
                'answer'    => $question->answer ? $question->answer->answer : null,
                'is_true'   => $question->answer ? $question->answer->is_true : null,
            ];
        }
    }

    /**
     * Show the questions list with prombet to select which question
     * want to answer and then handle his answer.
     *
     * @return void
     */
    private function chooseQuestionToAnswer() : void
    {
        $questionId = $this->console->choice(__('Please select question to answer:'), $this->options);
        $this->handleAnswer($questionId ? array_search($questionId, $this->options) : null);
    }

    /**
     * Show the user progress with each question
     *
     * @return void
     */
    private function showProgress()
    {
        $allQuestions = $this->questionRepository->list(['id', 'question']);
        $this->transformProgressList($allQuestions);

        $this->console->info( ' ************ Your progress is ************');

        foreach ($this->progress as $option) {
            $validate = $option['is_true'] ? __('True') :  __('False');
            $this->console->info( ' Question: ' . $option['question']);
            if(null !== $option['is_true'])
                $this->console->info( ' Answer: '   . $option['answer'] . '('.$validate .')');
            $this->console->info( ' ');
        }
        $this->console->info( ' *******************************************');
    }

    /**
     * Get and store user's answer and show his progress if finished all questions
     *  and then show the main menu options.
     *
     * @param int $questionId
     * @return void
     */
    private function handleAnswer($questionId) : void
    {
        if(! $this->answerRepository->hasAnswer($questionId)) {
            $this->answerTheQuestion($questionId);
        } else {
            $this->console->info(__('You answered this question'));
        }
        $this->showOptions($this->console);
    }

    private function answerTheQuestion($questionId)
    {
        $answer     = $this->console->ask(__('Type your answer'));
        $question   = $this->questionRepository->resetQuery()->find($questionId);
        $isTrue     = (isset($question) && $question->valid_answer == $answer)
            ? AnswerOptionsEnum::TRUE
            : AnswerOptionsEnum::FALSE;

        $this->answerRepository->create([
            'question_id'   => $questionId,
            'answer'        => $answer,
            'is_true'       => $isTrue
        ]);
        $this->console->info(__('Answer Added successfully'));

        if( ! $this->questionRepository->hasUnAnsweredQuestions() ) {
            $this->showProgress();
        }
    }

    /**
     * Terminate the program
     *
     * @return void
     */
    private function exit() : void
    {
        $this->console->info(__('Finish'));
        exit();
    }

    /**
     * Get array of all available options in main menu.
     *
     * @return array
     */
    private function getAllOptions() : array
    {
        return [
            QAOptionsEnum::ADD          => __('Add question'),
            QAOptionsEnum::VIEW         => __('View questions'),
            QAOptionsEnum::SHOW_ANSWERS => __('Show answers'),
            QAOptionsEnum::QA_EXIT      => __('Exit')
        ];
    }

    /**
     * Reset all user progress.
     *
     * @param $console
     * @return void
     */
    public function resetAnswers($console) : void
    {
        $this->answerRepository->destroy();
        $console->info(__('All progress has been reset'));
    }

}
