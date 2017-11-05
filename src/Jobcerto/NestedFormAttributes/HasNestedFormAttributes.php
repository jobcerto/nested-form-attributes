<?php 

namespace Jobcerto\NestedFormAttributes;


use Illuminate\Support\Collection;

trait HasNestedFormAttributes
{

    /**
     * Boot the trait and create an macro to work 
     * with collections and make sure all fields
     * in the array are in correct order
     * 
     * @return mixed 
     */
    public static function bootHasNestedFormAttributes()
    {

        Collection::macro('transpose', function($keys = null) {

            $keys = $keys ?: range(0, static::count() - 1);
            $items = array_map(function(...$items) use ($keys) {

                return array_combine($keys, $items);
            }, ...static::values());

            return new static($items);
        });
    }


    /**
     * Saved model for use on many to many relations
     * @var
     */
    protected $savedModel;


    /**
     * Handler with nested relations
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function handlerNestedAttributes(array $attributes)
    {

        $nestedAttributes = $this->sortNestedAttributes($attributes);

        foreach( $nestedAttributes as $relation => $collection ) {

            $relationName = $this->getRelationName($collection);

            $method = $this->getMethodName($relation);

            $this->{$method}($collection->first(), $relationName);

        }

        return $this->load($this->nested);
    }


    /**
     * Save the BelongsTo Relation
     *
     * @param $items
     * @param $method
     */
    protected function saveBelongsTo($items, $method)
    {

        collect($items)->each(function($attributes) use ($method) {

            $this->{$method}()->associate($attributes);
        });

        $this->save();

        // Because this relation interacts directly with an saved model
        //We have to return this saved model in order to work with all
        //other kind of relationships
        $this->savedModel = $this;
    }


    /**
     * Save the BelongsToMany Relation
     *
     * @param $items
     * @param $method
     */
    protected function saveBelongsToMany($items, $method)
    {

        collect($items)->each(function($attributes) use ($method) {

            $this->{$method}()->attach($attributes);
        });

    }


    /**
     * save the HasMany Relation
     *
     * @param $items
     * @param $method
     */
    protected function saveHasMany($items, $method)
    {

        $items = $items[ $method ];

        $keys = array_keys($items);

        collect($items)->transpose($keys)->map(function($data) {

            return $data;
        })->each(function($attributes) use ($method) {

            $this->{$method}()->create($attributes);
        });
    }


    /**
     * save the MorphToMany Relation
     *
     * @param $items
     * @param $method
     */
    protected function saveMorphToMany($items, $method)
    {

        $this->saveBelongsToMany($items, $method);
    }


    /**
     * save the MorphMany Relation
     *
     * @param $items
     * @param $method
     */
    protected function saveMorphMany($items, $method)
    {

        $this->saveHasMany($items, $method);
    }


    /**
     * save the HasMany Relation
     *
     * @param $items
     * @param $method
     */
    protected function saveHasOne($items, $method)
    {

        collect($items)->each(function($attributes) use ($method) {

            $this->{$method}()->create($attributes);
        });
    }


    /**
     * Sort attributes in order of relations
     *
     * @param $attributes
     *
     * @return array
     */
    private function sortNestedAttributes($attributes)
    {

        $order = $this->getNestedRelations($attributes);

        return array_sort($order, function($values, $relation) {

            return $relation;
        });
    }


    /**
     * Only retrieve allowed attributes
     *
     * @param $attributes
     *
     * @return array
     */
    private function allowed($attributes): array
    {

        return array_only($attributes, $this->nested);
    }


    /**
     * Get the short name of an given class
     *
     * @param $relation
     *
     * @return string
     */
    private function getClassShortName($relation): string
    {

        return (new \ReflectionClass($this->{$relation}()))->getShortName();
    }


    /**
     * Get an list of allowed relations and his attribtues
     *
     * @param $attributes
     *
     * @return mixed
     */
    private function getNestedRelations($attributes)
    {

        $collection = collect($this->allowed($attributes));

        $nestedRelations = $collection->mapToGroups(function($values, $relation) {

            return [
                $this->getClassShortName($relation) => [
                    $relation => $values,
                ],
            ];
        });

        return $nestedRelations;
    }


    /**
     * Get the method name
     *
     * @param $relation
     *
     * @return string
     */
    private function getMethodName($relation)
    {

        return sprintf('save%s', $relation);
    }


    /**
     * Get the relationship name
     *
     * @param $collection
     *
     * @return mixed
     */
    private function getRelationName($collection)
    {

        return array_keys($collection[0])[0];
    }

}
