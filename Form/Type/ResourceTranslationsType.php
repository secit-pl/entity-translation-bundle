<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Form\Type;

use SecIT\EntityTranslationBundle\Translations\TranslationInterface;
use SecIT\EntityTranslationBundle\Translations\TranslationLocaleProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locales;
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
     * @var bool
     */
    private $renderAsCollection = true;

    /**
     * @param TranslationLocaleProvider $localeProvider
     */
    public function __construct(TranslationLocaleProvider $localeProvider)
    {
        $this->definedLocalesCodes = $localeProvider->getDefinedLocalesCodes();
        $this->defaultLocaleCode = $localeProvider->getDefaultLocaleCode();
        $this->renderAsCollection = $localeProvider->hasMultipleLocalesCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$this->renderAsCollection) {
            $entryOptions = $options['entry_options']($this->defaultLocaleCode);
            if (!isset($entryOptions['constraints'])) {
                $entryOptions['constraints'] = [];
            }
            $entryOptions['constraints'][] = new Valid();
            $entryOptions['label'] = false;

            $builder->add($this->defaultLocaleCode, $options['entry_type'], $entryOptions);
        }

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
        if ($this->renderAsCollection) {
            $resolver->setDefaults([
                'entries' => $this->definedLocalesCodes,
                'entry_name' => function (string $localeCode): string {
                    return $localeCode;
                },
                'constraints' => [
                    new Valid(),
                ],
            ])
            ->setNormalizer('entry_options', function ($options, $additionalValues) {
                return function (string $localeCode) use ($additionalValues): array {
                    $entryOptions = [
                        'required' => $localeCode === $this->defaultLocaleCode,
                        'label' => Locales::getName($localeCode),
                    ];

                    if (is_array($additionalValues)) {
                        return array_merge($entryOptions, $additionalValues);
                    }

                    return $entryOptions;
                };
            });
        } else {
            $resolver->setDefault('entry_type', null);
            $resolver->isRequired('entry_type');
            $resolver->setAllowedTypes('entry_type', 'string');

            $resolver->setDefault('entry_options', function () {
                return [];
            });
            $resolver->setAllowedTypes('entry_options', ['array', 'callable']);
            $resolver->setNormalizer('entry_options', FixedCollectionType::optionalCallableNormalizer());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        if ($this->renderAsCollection) {
            return FixedCollectionType::class;
        }

        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'resource_translations';
    }
}
