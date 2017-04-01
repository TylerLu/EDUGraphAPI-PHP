<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

use ReflectionClass;

abstract class ParsableObject
{
    /**
     * Parse json data to the object
     *
     * @param string $json The json data
     *
     * @return void
     */
    public function parse($json)
    {
        $map = collect($this->mappingTable);
        $data = collect($json);
        if ($map->isEmpty() or $data->isEmpty()) {
            return;
        }
        foreach ($map as $key => $value) {
            if (!$data->has($value)) {
                continue;
            }
            $class = new ReflectionClass(get_class($this));
            if ($class->hasProperty($key)) {
                $property = $class->getProperty($key);
                $dataValue = $data[$value];
                $elementTypes = collect($this->arrayElementTypeTable);
                if ($elementTypes && $elementTypes->has($key)) {
                    if (!is_array($dataValue)) {
                        continue;
                    }
                    $proValue = $property->getValue($this);
                    if (is_null($proValue)) {
                        $proValue = [];
                    }
                    foreach ($dataValue as $_key => $_value) {
                        $obj = new $elementTypes[$key]();
                        $obj->parse($_value);
                        $proValue[] = $obj;
                    }
                    $property->setValue($this, $proValue);
                } else {
                    $property->setValue($this, $dataValue);
                }
            }
        }
    }

    /**
     * Add mappings between json data and properties
     *
     * @param array $mappings The mappings between json data and properties
     *
     * @return void
     */
    public function addPropertyMappings($mappings)
    {
        if (!is_array($this->mappingTable)) {
            $this->mappingTable = [];
        }
        $this->mappingTable = array_merge($this->mappingTable, $mappings);
    }

    /**
     * Add array element types
     *
     * @param array $types The array element types
     *
     * @return void
     */
    public function addArrayElementTypes($types)
    {
        if (!is_array($this->arrayElementTypeTable)) {
            $this->arrayElementTypeTable = [];
        }
        $this->arrayElementTypeTable = array_merge($this->arrayElementTypeTable, $types);
    }

    /**
     * The table defining how to map the json data to the object properties
     *
     * @var array
     */
    protected $mappingTable;

    /**
     * The table defining the element type of array properties
     *
     * @var array
     */
    protected $arrayElementTypeTable;
}