<?php


namespace App\Repositories;

use App\Answer;

class AnswerRepository extends BaseRepository
{
    /**\ The model to build the query.
     * @var Answer
     */
    protected $model;

    /**
     * AnswerRepository constructor.
     * @param Answer $model
     */
    public function __construct(Answer $model)
    {
        $this->model = $model;
    }

    public function destroy()
    {
        $allAnswers = $this->model->get('id');
        $this->model->destroy($allAnswers->pluck('id')->toArray());
    }


}
