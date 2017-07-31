<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\ViewModel;

class ArrayResult extends ParsableObject
{
    /**
     * Create a new instance.
     *
     * @param mixed $elementClass The class of the array element
     *
     * @return void
     */
    public function __construct($elementClass)
    {
        $this->value = [];
        $this->addPropertyMappings(
            [
                "nextLink" => "@odata.nextLink",
                "value" => "value"
            ]);
        $this->addArrayElementTypes(["value" => $elementClass]);
    }

    /**
     * Parse json data to the object
     *
     * @param string $json The json data
     *
     * @return void
     */
    public function parse($json)
    {
        parent::parse($json);
        $this->skipToken = $this->getSkipToken();
    }

    /**
     * Get the skip token from the nextLink property
     *
     * @return string The skip token
     */
    private function getSkipToken()
    {
        $pattern = '/\$skiptoken=([^&]+)/';
        $match = [];
        preg_match($pattern, $this->nextLink, $match);
        if (count($match) == 2) {
            return $match[1];
        }
        return '';
    }

    public $value;
    public $nextLink;
    public $skipToken;
}