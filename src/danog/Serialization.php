<?php
/*
Copyright 2016-2017 Daniil Gentili
(https://daniil.it)
This file is part of MagicalSerializer.
MagicalSerializer is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
MagicalSerializer is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Affero General Public License for more details.
You should have received a copy of the GNU General Public License along with the MagicalSerializer.
If not, see <http://www.gnu.org/licenses/>.
*/

namespace danog;

class Serialization
{
    public static function unserialize($data)
    {
        foreach (get_declared_classes() as $class) {
            if (isset(class_uses($class)['danog\Serializable']) || $class === 'Volatile') {
                $namelength = strlen($class);
                $data = explode('O:'.$namelength.':"'.$class.'":', $data);
                $stringdata = array_shift($data);
                foreach ($data as $chunk) {
                    list($attributecount, $value) = explode(':{', $chunk, 2);
                    $attributecount++;
                    $stringdata .= 'O:17:"danog\PlaceHolder":'.$attributecount.':{s:21:"originalclassnamepony";s:'.$namelength.':"'.$class.'";'.$value;
                }
                $data = $stringdata;
            }
        }

        return \danog\Serialization::extractponyobject(unserialize($data));
    }

    public static function extractponyobject($orig)
    {
        if (isset($orig->realactualponyobject)) {
            return \danog\Serialization::extractponyobject($orig->realactualponyobject);
        }
        if (is_array($orig) || $orig instanceof \Volatile) {
            foreach ($orig as $key => $value) {
                $orig[$key] = \danog\Serialization::extractponyobject($value);
            }

            return $orig;
        }
        if (is_object($orig)) {
            foreach ($orig as $key => $value) {
                $orig->{$key} = \danog\Serialization::extractponyobject($value);
            }
        }

        return $orig;
    }

    public static function serialize($object)
    {
        $object = explode('O:17:"danog\PlaceHolder":', serialize(self::createserializableobject($object)));
        $newobject = array_shift($object);
        foreach ($object as $chunk) {
            list($attributecount, $value) = explode(':{', $chunk, 2);
            $attributecount--;
            list($pre, $value) = explode('s:21:"originalclassnamepony";s:', $value, 2);
            list($length, $value) = explode(':', $value, 2);
            $classname = substr($value, 1, $length);
            $value = $pre.substr($value, $length + 3);
            $newobject .= 'O:'.strlen($classname).':"'.$classname.'":'.$attributecount.':{'.$value;
        }

        return $newobject;
    }

    public static function createserializableobject($orig)
    {
        if (is_object($orig) && method_exists($orig, 'fetchserializableobject')) {
            return $orig->fetchserializableobject();
        }
        if ($orig instanceof \Volatile) {
            $orig = self::createserializableobject(get_object_vars($orig));
        }
        if (is_array($orig) || $orig instanceof \Volatile) {
            foreach ($orig as $key => $value) {
                $orig[$key] = self::createserializableobject($value);
            }
        }

        return $orig;
    }
}
