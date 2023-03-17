<?php

namespace App\Validator;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueUserValidator extends ConstraintValidator
{
    public function __construct(private readonly DocumentManager $dm)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var \App\Validator\UniqueUser $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $existingUser = $this->dm->createQueryBuilder(User::class)
            ->field('email')->equals($value)
            ->getQuery()
            ->getSingleResult();

        if ($existingUser) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
