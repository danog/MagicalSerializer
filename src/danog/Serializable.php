<?php
/*
Copyright 2016-2017 Daniil Gentili
(https://daniil.it)
This file is part of SerializableVolatile.
SerializableVolatile is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
SerializableVolatile is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Affero General Public License for more details.
You should have received a copy of the GNU General Public License along with the SerializableVolatile.
If not, see <http://www.gnu.org/licenses/>.
*/

namespace danog;

trait Serializable
{
    public function __construct(...$params)
    {
        if (count($params) === 1 && is_array($params[0]) && isset($params[0]['originalclassnamepony'])) {
            unset($params[0]['originalclassnamepony']);
            foreach ($params[0] as $key => $value) {
                $this->{$key} = \danog\Serialization::extractponyobject($value);
            }

            return;
        }
        if (method_exists($this, '___construct')) {
            $this->___construct(...$params);
        }
    }

    public function fetchserializableobject()
    {
        $values = (array)$this;
        if (method_exists($this, '__sleep')) {
            $newvalues = [];
            foreach ($this->__sleep() as $key) {
                $newvalues[$key] = $values[$key];
            }
            $values = $newvalues;
        }
        return new \danog\PlaceHolder(get_class($this), $values);
    }
}
