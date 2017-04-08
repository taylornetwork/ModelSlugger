<?php

namespace TaylorNetwork\ModelSlugger;

use Illuminate\Database\Eloquent\Model;

class SluggerObserver
{
    /**
     * On model saving, add slug.
     * 
     * @param Model $model
     */
    public function saving(Model $model)
    {
        (new Slugger($model))->slug();
    }
}