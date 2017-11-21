<?php

namespace RickSelby\LaravelRequestFieldTypes;

use Illuminate\Support\Collection;

class FieldTypes
{
    /** @var Collection */
    private $fieldTypes;

    public function __construct()
    {
        $this->fieldTypes = new Collection();
    }

    /**
     * Register a field type
     *
     * @param string $class
     * @throws \Exception
     */
    public function register(string $class)
    {
        $fieldType = new $class;
        if (!$fieldType instanceof FieldTypeInterface) {
            throw new \Exception('Registered field type must implement FieldTypeInterface');
        }

        $this->fieldTypes->put($fieldType->getIdentifier(), $fieldType);
    }

    /**
     * Set input fields for a field type, given by its identifier
     *
     * @param string $fieldType
     * @param array $fieldNames
     */
    public function setInputsFor($fieldType, $fieldNames)
    {
        $this->getIdentifier($fieldType)->setInputFields($fieldNames);
    }

    /**
     * Get a field by its identifier
     *
     * @param string $fieldType
     *
     * @return FieldTypeInterface
     * @throws \Exception
     */
    protected function getIdentifier($fieldType)
    {
        if ($this->fieldTypes->has($fieldType)) {
            return $this->fieldTypes->get($fieldType);
        } else {
            throw new \Exception('Field type "'.$fieldType.'" not found');
        }
    }

    /**
     * Get a list of rules for all registered fields
     *
     * @return Collection
     */
    public function getRules(): Collection
    {
        return $this->fieldTypes->reduce(function (Collection $carry, FieldTypeInterface $fieldType) {
            return $carry->merge($fieldType->getRules());
        }, new Collection());
    }

    /**
     * Take the request input values and allow each field to modify them as required
     *
     * @param array $request
     *
     * @return array
     */
    public function modifyInputAfterValidation($request)
    {
        return $this->fieldTypes->reduce(function ($requestValues, FieldTypeInterface $fieldType) {
            return $fieldType->modifyInputAfterValidation($requestValues);
        }, $request);
    }
}
