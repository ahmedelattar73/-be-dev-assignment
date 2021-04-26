<?php


namespace App\Repositories;


use Illuminate\Database\Eloquent\Collection;

class BaseRepository
{

    /** Create new item.
     * @param $data
     */
    public function create($data) : void
    {
        $this->model->create($data);
    }

    /** Get all item query
     * @param array|null $attributes
     * @return Collection
     */
    public function list(array $attributes = null) : Collection
    {
        return $this->model->get($attributes);
    }

    /** Find an item by id.
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }


}
