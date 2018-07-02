<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Entity;

use SecIT\EntityTranslationBundle\Translations\TranslatableInterface;
use SecIT\EntityTranslationBundle\Translations\TranslationInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractTranslation.
 *
 * @author Tomasz Gemza
 *
 * @ORM\MappedSuperclass()
 */
abstract class AbstractTranslation implements TranslationInterface
{
    /**
     * @var null|string
     *
     * @ORM\Column(type="string", length=8)
     */
    private $locale;

    /**
     * Dynamically mapped in TranslatableSubscriber.
     *
     * @var null|TranslatableInterface
     *
     * @see \SecIT\EntityTranslationBundle\EventSubscriber\TranslatableSubscriber
     */
    protected $translatable;

    /**
     * {@inheritdoc}
     */
    public function getTranslatable(): TranslatableInterface
    {
        return $this->translatable;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslatable(?TranslatableInterface $translatable): void
    {
        if ($translatable === $this->translatable) {
            return;
        }

        $previousTranslatable = $this->translatable;
        $this->translatable = $translatable;
        if (null !== $previousTranslatable) {
            $previousTranslatable->removeTranslation($this);
        }

        if (null !== $translatable) {
            $translatable->addTranslation($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }
}
