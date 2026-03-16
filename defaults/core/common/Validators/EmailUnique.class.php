<?php
namespace YoudsFramework\Validator;
use YoudsFramework\Validator;
use Defaults\Core\Models\Orm\Auth\User;

class EmailUnique extends Validator
{
    public function validate()
    {
        // lookup name
        $email = User::where('email', $this->getData($this->getArgument()))->first();

        if (isset($email) && $email != NULL):
            $this->throwError();
            return false;
        endif;

        $this->export($email);

        return true;
    }
}

?>
