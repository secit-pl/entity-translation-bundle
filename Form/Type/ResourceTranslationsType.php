<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Form\Type;

use SecIT\EntityTranslationBundle\Translations\TranslationInterface;
use SecIT\EntityTranslationBundle\Translations\TranslationLocaleProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

final class ResourceTranslationsType extends AbstractType
{
    /**
     * @var string[]
     */
    private $definedLocalesCodes;

    /**
     * @var string
     */
    private $defaultLocaleCode;

    /**
     * @param TranslationLocaleProvider $localeProvider
     */
    public function __construct(TranslationLocaleProvider $localeProvider)
    {
        $this->definedLocalesCodes = $localeProvider->getDefinedLocalesCodes();
        $this->defaultLocaleCode = $localeProvider->getDefaultLocaleCode();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var TranslationInterface[] $translations */
            $translations = $event->getData();
            $translatable = $event->getForm()->getParent()->getData();

            foreach ($translations as $localeCode => $translation) {
                if (null === $translation) {
                    unset($translations[$localeCode]);

                    continue;
                }

                $translation->setLocale($localeCode);
                $translation->setTranslatable($translatable);
            }

            $event->setData($translations);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entries' => $this->definedLocalesCodes,
            'entry_name' => function (string $localeCode): string {
                return $localeCode;
            },
            'entry_options' => function (string $localeCode): array {
                return [
                    'required' => $localeCode === $this->defaultLocaleCode,
                    'label' => Intl::getLocaleBundle()->getLocaleName($localeCode),
                ];
            },
            'constraints' => [
                new Valid(),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return FixedCollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'resource_translations';
    }
}
