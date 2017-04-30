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

class Serialization
{
    public static $volatile = 'O:25:"danog\PlaceHolderVolatile":';
    public static $threaded = 'O:25:"danog\PlaceHolderThreaded":';

    public static function unserialize($data)
    {
        foreach (get_declared_classes() as $class) {
            if (($volatile = is_subclass_of($class, 'danog\SerializableVolatile')) || is_subclass_of($class, 'danog\SerializableThreaded')) {
                $namelength = strlen($class);
                $data = explode('O:'.$namelength.':"'.$class.'":', $data);
                $stringdata = array_shift($data);
                foreach ($data as $chunk) {
                    list($attributecount, $value) = explode(':{', $chunk, 2);
                    $attributecount++;
                    $stringdata .= ($volatile ? self::$volatile : self::$threaded).$attributecount.':{s:21:"originalclassnamepony";s:'.$namelength.':"'.$class.'";'.$value;
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
        $object = serialize(self::createserializableobject($object));
        foreach (['danog\PlaceHolderVolatile', 'danog\PlaceHolderThreaded'] as $class) {
            $object = explode('O:'.strlen($class).':"'.$class.'":', $object);
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
            $object = $newobject;
        }

        return $object;
    }

    public static function createserializableobject($orig)
    {
        if (is_object($orig) && method_exists($orig, 'fetchserializableobject')) {
            return $orig->fetchserializableobject();
        }
        if ($orig instanceof \Volatile) {
            $orig = self::createserializableobject((array) $orig);
        }
        if (is_array($orig) || $orig instanceof \Volatile) {
            foreach ($orig as $key => $value) {
                $orig[$key] = self::createserializableobject($value);
            }
        }

        return $orig;
    }
}
