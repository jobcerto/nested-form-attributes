<?php 

namespace Jobcerto\NestedFormAttributes;

trait HasNestedFormAttributes
{

    /**
     * Saved model for use on many to many relations
     * @var
     */
    protected $savedModel;


    /**
     * Handler with nested relations
     *
     * @param array $attributes
     */
    public function handlerNestedAttributes(array $attributes)
    {

        $collection = $this->sortNestedAttributes($attributes);
        foreach( $collection as $relation => $values ) {
            $method = sprintf('save%s', $relation);
            $this->{$method}($values);
        }

        return $this->load($this->nested);

    }


    /**
     * Save the BelongsTo Relation
     *
     * @param $collection
     */
    protected function saveBelongsTo($collection)
    {

        $collection->each(function($attributes) {

            $method = array_keys($attributes)[0];
            $values = array_values($attributes)[0];
            $this->{$method}()->associate($values);
        });
        $this->save();

        // Because this relation interacts directly with an saved model
        //We have to retorn this saved model in order to work with all
        //other kind of relationships
        $this->savedModel = $this;
    }


    /**
     * Save the BelongsToMany Relation
     *
     * @param $collection
     */
    protected function saveBelongsToMany($collection)
    {

        $collection->each(function($attributes) {

            $method = array_keys($attributes)[0];
            $values = array_values($attributes)[0];

            $this->{$method}()->attach($values);
        });

    }


    /**
     * save the HasMany Relation
     *
     * @param $collection
     */
    protected function saveHasMany($collection)
    {

        $collection->each(function($attributes) {

            $method = array_keys($attributes)[0];
            $values = array_values($attributes)[0];

            $this->{$method}()->createMany($values);
        });
    }


    /**
     * save the MorphToMany Relation
     *
     * @param $collection
     */
    protected function saveMorphToMany($collection)
    {

        $this->saveBelongsToMany($collection);
    }


    /**
     * save the MorphMany Relation
     *
     * @param $collection
     */
    protected function saveMorphMany($collection)
    {

        $this->saveHasMany($collection);
    }


    /**
     * save the HasMany Relation
     *
     * @param $collection
     */
    protected function saveHasOne($collection)
    {

        $collection->each(function($attributes) {

            $method = array_keys($attributes)[0];
            $values = array_values($attributes)[0];

            $this->{$method}()->create($values);
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
                    $relation => $values
                ]
            ];
        });

        return $nestedRelations;
    }
}
