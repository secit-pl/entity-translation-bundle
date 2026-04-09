<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Translations;

/**
 * Interface TranslationInterface.
 *
 * @author Tomasz Gemza
 */
interface TranslationInterface
{
    /**
     * Get translatable element.
     *
     * @return TranslatableInterface
     */
    public function getTranslatable(): TranslatableInterface;

    /**
     * Set translatable element.
     */
    public function setTranslatable(?TranslatableInterface $translatable): void;

    /**
     * Get locale.
     */
    public function getLocale(): ?string;

    /**
     * Set locale.
     */
    public function setLocale(?string $locale): void;
}
