<?php

namespace RickSelby\LaravelRequestFieldTypes;

use Illuminate\Foundation\Http\FormRequest;

/**
 * An extended requests that allows the use of the Fields class to manage defined fields.
 *
 * Class RequestFieldsRequest
 */
abstract class FieldTypesRequest extends FormRequest
{
    use RulesTrait {
        setRules as private traitSetRules;
    }

    /** @var FieldTypes */
    private $fields;

    /** @var string[] */
    private $fieldOrder = [];

    public function __construct(FieldTypes $fields, array $query = [], array $request = [], array $attributes = [],
                                array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->fields = $fields;
    }

    /**
     * Override validation, to modify the request data after successful validation.
     */
    public function validate()
    {
        $this->defineRules();
        parent::validate();
        $this->runAfterValidate();
    }

    /**
     * Set input fields for a field type.
     *
     * @param string $fieldType
     * @param array $fieldNames
     *
     * @throws \Exception
     */
    public function setInputsFor($fieldType, array $fieldNames)
    {
        $this->fields->setInputsFor($fieldType, $fieldNames)->each(function ($inputField) {
            $this->addFieldToOrder($inputField);
        });
    }

    /**
     * Set rules for an input field.
     *
     * @param $inputField
     * @param array $rules
     */
    public function setRules($inputField, array $rules)
    {
        $this->traitSetRules($inputField, $rules);
        $this->addFieldToOrder($inputField);
    }

    /**
     * Define your rules here.
     */
    abstract public function defineRules();

    /**
     * Get all rules, defined in the fields and locally.
     *
     * @return array
     */
    public function rules()
    {
        return $this->fields->getRules()
            ->union($this->getRules())
            ->map->implode('|')
            ->setKeyOrder($this->fieldOrder)
            ->toArray();
    }

    /**
     * Replace the input values with modified values from the defined fields.
     */
    protected function runAfterValidate()
    {
        $this->replace($this->fields->modifyInputAfterValidation($this->all()));
    }

    /**
     * Directly set the field order.
     *
     * @param array $order
     */
    public function setFieldOrder(array $order)
    {
        $this->fieldOrder = $order;
    }

    /**
     * Add a single field to the field order.
     *
     * @param $inputField
     */
    private function addFieldToOrder($inputField)
    {
        $this->fieldOrder[] = $inputField;
    }
}
