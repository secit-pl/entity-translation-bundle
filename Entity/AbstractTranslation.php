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
 */
#[ORM\MappedSuperclass]
abstract class AbstractTranslation implements TranslationInterface
{
    #[ORM\Column(type: 'string', length: 8)]
    protected ?string $locale = null;

    /**
     * Dynamically mapped in TranslatableSubscriber.
     *
     * @see \SecIT\EntityTranslationBundle\EventSubscriber\TranslatableSubscriber
     */
    protected ?TranslatableInterface $translatable = null;

    public function getTranslatable(): TranslatableInterface
    {
        return $this->translatable;
    }

    public function setTranslatable(?TranslatableInterface $translatable): void
    {
        if ($translatable === $this->translatable) {
            return;
        }

        $previousTranslatable = $this->translatable;
        $this->translatable = $translatable;

        $previousTranslatable?->removeTranslation($this);
        $translatable?->addTranslation($this);
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }
}
