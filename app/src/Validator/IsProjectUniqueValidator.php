<?php

namespace App\Validator;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsProjectUniqueValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * @param IsProjectUnique $constraint
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        // Ensure used in a form or Project entity
        $root = $this->context->getRoot();
        $project = $root instanceof Form ? $root->getData() : $root;

        if (!$project instanceof Project) {
            $this->context
                ->buildViolation($constraint->useInProjectMessage)
                ->addViolation()
            ;

            return;
        }

        // Ensure project is unique
        $matchingProject = $this->em->getRepository(Project::class)->findOneBy([
            'projectId' => $project->getProjectId(),
            'branch' => $project->getBranch(),
        ]);
        if ($matchingProject) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ branch }}', $project->getBranch())
                ->addViolation()
            ;
        }
    }
}
