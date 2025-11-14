<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Interface\NameableEntityInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class AbstractGuardianCrudController extends AbstractCrudController
{
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)

            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)

            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_EDIT, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye');
            })

            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, Action::DELETE])
            ->reorder(Crud::PAGE_EDIT, [Action::INDEX])
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud)->showEntityActionsInlined();

        $fqcn = $this::getEntityFqcn();
        if (in_array(NameableEntityInterface::class, class_implements($fqcn) ?: [])) {
            /* @var NameableEntityInterface $fqcn */
            $crud->setEntityLabelInSingular($fqcn::getSingular())
                ->setEntityLabelInPlural($fqcn::getPlural())
            ;
        }

        return $crud;
    }
}
