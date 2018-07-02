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
     *
     * @param TranslatableInterface|null $translatable
     */
    public function setTranslatable(?TranslatableInterface $translatable): void;

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale(): ?string;

    /**
     * Set locale.
     *
     * @param string|null $locale
     */
    public function setLocale(?string $locale): void;
}
