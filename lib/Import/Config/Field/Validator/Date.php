<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Date implements Validator
{
    private $empty;

    public function __construct($empty = false)
    {
        $this->setCanEmpty($empty);
    }

    public function setCanEmpty($empty = false)
    {
        $this->empty = (bool)$empty;
        return $this;
    }

    public function canEmpty()
    {
        return (bool)$this->empty;
    }

    public function validate($value)
    {
        $valid = false;
        if (($this->canEmpty() && $value == '')
            || \Is::date($value)
            || \Is::dateUk($value)
            || \Is::dateUk($value)
            || \Is::dateWithoutSeparator($value)
        ) {
            $valid = true;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas une date valide";
    }
}
