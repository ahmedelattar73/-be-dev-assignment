<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    protected $fillable = [
      'question',
      'valid_answer'
    ];

    /**
     * Get the user answer.
     *
     * @return Answer
     */
    public function answer()
    {
        return $this->hasOne(Answer::class);
    }

}
