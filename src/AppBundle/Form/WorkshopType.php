<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkshopType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('start', null, [
            'widget' => 'single_text',
            'html5' => false,
            'attr' => ['class' => 'bs-datetime'],
            'format' => 'yyyy-MM-dd HH:mm',
        ]);
        $builder->add('end', null, [
            'widget' => 'single_text',
            'html5' => false,
            'attr' => ['class' => 'bs-datetime'],
            'format' => 'yyyy-MM-dd HH:mm',
        ]);
        $builder->add('model', null, [
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.isModel = true');
            },
        ]);
        $builder->add('title');
        $builder->add('peopleMax');
        $builder->add('description');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Workshop'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_workshop';
    }


}
