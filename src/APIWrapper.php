<?php
/**
 * LICENSE
 *
 Copyright 2015 Grégory Saive (greg@evias.be)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 *
 * @package Sociall
 * @author Grégory Saive <greg@evias.be>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
**/

namespace sociall;

class APIWrapper
{
    /**
     * @var array   $_options
     **/
    protected $_options = [];

    /**
     * APIWrapper Constructor
     * @param   array   $opts
     **/
    public function __construct(array $opts = [])
    {
        $this->setOptions($opts);
    }

    /**
     * The setOptions() method is called automatically by the
     * constructor to populate the current object's options.
     * Any field for which a setter method is available can be
     * set using this method. Following guidelines apply to the
     * options field names :
     * - Underscores (_) are replaced by Spaces.
     * - Words are Uppercased and Spaces removed.
     *
     * @param   array   $opts
     * @return  sociall\APIWrapper
     */
    protected function setOptions(array $opts)
    {
        foreach ($opts as $key => $value) {
            $words  = str_replace("_", " ", strtolower($key));
            $method = "set" . str_replace(" ", "", ucwords($words));

            $this->_options[$key] = $value;
            if (method_exists($this, $method))
                // call "setter"
                $this->{$method}($value);
        }

        return $this;
    }
}
