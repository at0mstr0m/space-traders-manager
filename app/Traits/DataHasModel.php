<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Model;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

trait DataHasModel
{
    public static function getModelClass(): string
    {
        $classBaseName = Str::beforeLast(class_basename(static::class), 'Data');
        $modelClass = "App\\Models\\{$classBaseName}";

        if (class_exists($modelClass)) {
            return $modelClass;
        }

        throw new FileNotFoundException("Model class for {$classBaseName} not found");
    }

    public function makeModel(array|Arrayable $attributes): Model
    {
        return static::getModelClass()::new($attributes);
    }
}
