<?php

namespace Pum\Bundle\CoreBundle\Validator\Constraints;

use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\ConstraintValidator;

class PumUniqueEntityValidator extends ConstraintValidator
{
        /**
     * @var ObjectEntityManager
     */
    private $oem;

    /**
     * @param ObjectEntityManager $oem
     */
    public function __construct(ObjectEntityManager $oem)
    {
        $this->oem = $oem;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!is_array($constraint->fields) && !is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $fields = (array) $constraint->fields;

        if (0 === count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        if (null !== $constraint->em && $constraint->em instanceof ObjectEntityManager) {
            $em = $constraint->em;
        } else {
            $em = $this->oem;
        }

        $className = $this->context->getClassName();
        $class = $em->getClassMetadata($className);
        /* @var $class \Doctrine\Common\Persistence\Mapping\ClassMetadata */

        $repository = $em->getRepository($className);
        $criteria = array();

        foreach ($fields as $fieldName) {
            if (!$class->hasField($fieldName) && !$class->hasAssociation($fieldName)) {
                throw new ConstraintDefinitionException(sprintf("The field '%s' is not mapped by Doctrine, so it cannot be validated for uniqueness.", $fieldName));
            }

            $criteria[$fieldName] = $class->reflFields[$fieldName]->getValue($entity);

            if ($constraint->ignoreNull && null === $criteria[$fieldName]) {
                return;
            }

            if (null !== $criteria[$fieldName] && $class->hasAssociation($fieldName)) {
                /* Ensure the Proxy is initialized before using reflection to
                 * read its identifiers. This is necessary because the wrapped
                 * getter methods in the Proxy are being bypassed.
                 */
                $em->initializeObject($criteria[$fieldName]);

                $relatedClass = $em->getClassMetadata($class->getAssociationTargetClass($fieldName));
                $relatedId = $relatedClass->getIdentifierValues($criteria[$fieldName]);

                if (count($relatedId) > 1) {
                    throw new ConstraintDefinitionException(
                        "Associated entities are not allowed to have more than one identifier field to be ".
                        "part of a unique constraint in: ".$class->getName()."#".$fieldName
                    );
                }
                $criteria[$fieldName] = array_pop($relatedId);
            }
        }

        $repository = $em->getRepository(get_class($entity));
        $result = $repository->{$constraint->repositoryMethod}($criteria);

        /* If the result is a MongoCursor, it must be advanced to the first
         * element. Rewinding should have no ill effect if $result is another
         * iterator implementation.
         */
        if ($result instanceof \Iterator) {
            $result->rewind();
        } elseif (is_array($result)) {
            reset($result);
        }

        /* If no entity matched the query criteria or a single entity matched,
         * which is the same as the entity being validated, the criteria is
         * unique.
         */
        if (0 === count($result) || (1 === count($result) && $entity === ($result instanceof \Iterator ? $result->current() : current($result)))) {
            return;
        }

        $errorPath = null !== $constraint->errorPath ? $constraint->errorPath : $fields[0];

        $this->buildViolation($constraint->message)
            ->atPath($errorPath)
            ->setInvalidValue($criteria[$fields[0]])
            ->addViolation();
    }
}
