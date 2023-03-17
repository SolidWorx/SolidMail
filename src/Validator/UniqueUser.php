<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class UniqueUser extends Constraint
{
    public string $message = 'This email address is already registered. Do you want to log in instead?';
}
