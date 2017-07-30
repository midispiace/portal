<?php
/**
 * Offer type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class BookmarkType.
 *
 * @package Form
 */
class OfferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.title',
                'required' => true,
                'attr' => [
                    'max_length' => 128,
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['offer-default']]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['offer-default'],
                            'min' => 3,
                            'max' => 128,
                        ]
                    ),
                ],
            ]
        );
        $builder->add(
            'url',
            UrlType::class,
            [
                'label' => 'label.url',
                'required' => true,
                'attr' => [
                    'max_length' => 128,
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['offer-default']]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['offer-default'],
                            'min' => 3,
                            'max' => 128,
                        ]
                    ),
                    new Assert\Url(
                        ['groups' => ['offer-default']]
                    ),
                ],
            ]
        );
        $builder->add(
            'tags',
            TextType::class,
            [
                'required' => true,
                'attr' => [
                    'max_length' => 128,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'offer-default'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'offer_type';
    }

}