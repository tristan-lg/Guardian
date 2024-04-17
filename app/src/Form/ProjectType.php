<?php

namespace App\Form;

use App\Entity\Credential;
use App\Entity\DTO\ProjectApiDTO;
use App\Entity\Project;
use App\Service\GitlabApiService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class ProjectType extends AbstractType
{

    public function __construct(
        private readonly GitlabApiService $gitlabApiService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('name')
            ->add('credential', EntityType::class, [
                'class' => Credential::class,
                'empty_data' => null,
                'placeholder' => 'Selectionner un identifiant',
                'choice_label' => 'name',
            ])
        ;

        $builder->addDependent('projectId', 'credential', function (DependentField $field, ?Credential $credential): void {
            if (null === $credential) {
                return;
            }

            //Fetch projects available for the selected credential
            $client = $this->gitlabApiService->getClient($credential);
            $projectsData = $client->getAssociatedProjects();

            //Transform as int => string array
            $projects = [];
            foreach ($projectsData as $project) {
                $projects[sprintf('%d - %s', $project->id, $project->name)] = $project->id;
            }

            $field->add(ChoiceType::class, [
                'choices' => $projects,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
