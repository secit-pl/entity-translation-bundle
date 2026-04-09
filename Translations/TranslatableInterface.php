<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Translations;

use Doctrine\Common\Collections\Collection;

/**
 * Interface TranslatableInterface.
 *
 * @author Tomasz Gemza
 */
interface TranslatableInterface
{
    /**
     * Get translations.
     */
    public function getTranslations(): Collection;

    /**
     * Get translation.
     */
    public function getTranslation(?string $locale = null): TranslationInterface;

    /**
     * Check if translatable element has translation.
     */
    public function hasTranslation(TranslationInterface $translation): bool;

    /**
     * Add translation.
     */
    public function addTranslation(TranslationInterface $translation): void;

    /**
     * Remove translation.
     */
    public function removeTranslation(TranslationInterface $translation): void;

    /**
     * Set current locale.
     */
    public function setCurrentLocale(string $locale): void;

    /**
     * Set fallback locale.
     */
    public function setFallbackLocale(string $locale): void;
}
