<?php

namespace gear\validators;

use gear\interfaces\IObject;

class GObjectValidator
{
    /* Traits */
    /* Const */
    /* Private */
    /* Protected */
    /* Public */

    public function __invoke(IObject $object, $name, $value, $default = null)
    {
        return $this->validate($object, $name, $value, $default);
    }

    public function validate(IObject $object, $name, $value, $default = null) {}
}