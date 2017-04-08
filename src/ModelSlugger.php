<?php

namespace TaylorNetwork\ModelSlugger;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait ModelSlugger
{
    /**
     * Attach observer
     */
    public static function bootModelSlugger ()
    {
        static::observe(SluggerObserver::class);
    }

    /**
     * Allow for implicit route model binding if set.
     * 
     * @see Model::getRouteKeyName()
     * @return mixed
     */
    public function getRouteKeyName()
    {
        if (isset($this->sluggerRouteModelBind) && $this->sluggerRouteModelBind)
        {
            if (isset($this->sluggerConfig()['column']))
            {
                return $this->sluggerConfig()['column'];
            }
            return config('slugger.defaults.column');
        }
        return parent::getRouteKeyName();
    }

    /**
     * Find similar slugs with the same parents
     * 
     * @param Builder $query
     * @param Model $model
     * @param $config
     * @param $slug
     * @param null $parent_column
     * @return mixed
     */
    public function scopeParentSimilarSlugs (Builder $query, Model $model, $config, $slug, $parent_column = null)
    {
        if ($parent_column === null)
        {
            if (!isset($config['parentColumn']) || $config['parentColumn'] === null)
            {
                $parent_column = strtolower(class_basename($config['parent'])) . '_' . (new $config['parent'])->getKeyName();
            }
            else
            {
                $parent_column = $config['parentColumn'];
            }
        }

        return $query->where($parent_column, $model->$parent_column)->similarSlugs($config, $slug);
    }

    /**
     * Find similar slugs
     * 
     * @param Builder $query
     * @param $config
     * @param $slug
     * @return Builder|static
     */
    public function scopeSimilarSlugs (Builder $query, $config, $slug)
    {
        return $query->where($config['column'], $slug)->orWhere($config['column'], 'LIKE', $slug . $config['separator'] . '%');
    }

    /**
     * Slugger config
     * 
     * Minimum:
     *      return [ 'source' => 'sourceField' ];
     * 
     * @return mixed
     */
    abstract public function sluggerConfig();
}