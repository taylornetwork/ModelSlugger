<?php

namespace TaylorNetwork\ModelSlugger;

use Illuminate\Database\Eloquent\Model;
use Cocur\Slugify\Slugify;

class Slugger
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Slugger Config
     * 
     * @var array
     */
    protected $config;

    /**
     * Slug generator engine
     * 
     * @var object
     */
    protected $generator;

    /**
     * Slug generator method to call
     * 
     * @var string
     */
    protected $method;

    /**
     * Slugger constructor.
     * 
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->config = $model->sluggerConfig() + config('slugger.defaults');
        $this->generator = new Slugify([ 'separator' => $this->getConfig('separator') ]);
        $this->method = 'slugify';
    }

    /**
     * Generate slug for model
     */
    public function slug ()
    {
        $slug = $this->buildSlug($this->model->{$this->getConfig('source')});
        $this->saveSlug($slug);
    }

    /**
     * Build the slug
     * 
     * @param $text
     * @return string
     */
    public function buildSlug($text)
    {
        return $this->makeUnique($this->generator->{$this->method}($text));
    }

    /**
     * Make slug unique either with all slugs, similar parents, or not at all
     * 
     * @param $slug
     * @return string
     */
    public function makeUnique($slug)
    {
        switch (strtolower($this->getConfig('unique')))
        {
            case 'parent':
                $total = count($this->model->newQuery()->parentSimilarSlugs($this->model, $this->getConfig(), $slug)->get());
                return $this->appendIfNeeded($slug, $total);
                break;
            case 'all':
                $total = count($this->model->newQuery()->similarSlugs($this->getConfig(), $slug)->get());
                return $this->appendIfNeeded($slug, $total);
                break;
        }
        return $slug;
    }

    /**
     * Append a number to make slug unique if needed
     * 
     * @param $slug
     * @param $total
     * @return string
     */
    public function appendIfNeeded($slug, $total)
    {
        if ($total > 0)
        {
            return $slug . $this->getConfig('separator') . $total;
        }
        return $slug;
    }

    /**
     * Save the slug to the model
     * 
     * @param $slug
     */
    public function saveSlug($slug)
    {
        $this->model->setAttribute($this->getConfig('column'), $slug);
    }


    /**
     * Get config 
     * 
     * @param mixed $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        switch(gettype($key))
        {
            case 'string':
                return $this->config[$key];
                break;
            case 'array':
                return array_only($this->config, $key);
                break;
            case 'object':
                return array_only($this->config, (array) $key);
                break;
        }
        return $this->config;
    }
}