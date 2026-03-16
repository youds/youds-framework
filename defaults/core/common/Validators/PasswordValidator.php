<?php
namespace YoudsFramework\Validator;
use YoudsFramework\Validator;

class Password extends Validator
{
    public function validate()
    {
        $password = $this->getData($this->getArgument());

        // numeric check
        if (preg_replace('/[\d]+/', '', $password) == $password):
            $this->throwError();
            return false;
        endif;

        // symbol check
        if (preg_replace('/[\W]+/', '', $password) == $password):
            $this->throwError();
            return false;
        endif;

        $this->export($password);

        return true;
    }
}

?>
