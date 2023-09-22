<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    public function fillAndSave(array $attributes): bool
    {
        return $this->fill($attributes)->save();
    }
}
