<?php


namespace App\Repositories;


use App\Question;

class QuestionRepository extends BaseRepository
{
    /** The model to build th query.
     * @var Question
     */
    protected $model;

    /**
     * QuestionRepository constructor.
     * @param Question $model
     */
    public function __construct(Question $model)
    {
        $this->model = $model;
    }

    /** Reset the query builder.
     * @return $this
     */
    public function resetQuery()
    {
        $this->model = new Question();
        return $this;
    }

    /** Check if are still has question which not answered.
     * @return bool
     */
    public function hasUnAnsweredQuestions()
    {
        return $this->model->whereDoesntHave('answer')->count() ? true : false;
    }
}
