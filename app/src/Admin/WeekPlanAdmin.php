<?php

declare(strict_types=1);

namespace App\Admin;

use App\Document\WeekPlan;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @extends AbstractAdmin<WeekPlan>
 */
class WeekPlanAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('weekStartDate')
            ->add('shoppedSummary', null, [
                'label' => 'Shopped',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('weekStartDate');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('weekStartDate')
            ->add('days', null, [
                'label'    => 'Meals',
                'template' => 'admin/week_plan/show_days.html.twig',
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // ponytail: read-only admin — plans are managed via API; form is minimal for manual recovery
        $form->add('weekStartDate', TextType::class);
    }
}
