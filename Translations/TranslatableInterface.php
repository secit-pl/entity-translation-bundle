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
     *
     * @return Collection|TranslationInterface[]
     */
    public function getTranslations(): Collection;

    /**
     * Get translation.
     *
     * @param string|null $locale
     *
     * @return TranslationInterface
     */
    public function getTranslation(?string $locale = null): TranslationInterface;

    /**
     * Check if translatable element has translation.
     *
     * @param TranslationInterface $translation
     *
     * @return bool
     */
    public function hasTranslation(TranslationInterface $translation): bool;

    /**
     * Add translation.
     *
     * @param TranslationInterface $translation
     */
    public function addTranslation(TranslationInterface $translation): void;

    /**
     * Remove translation.
     *
     * @param TranslationInterface $translation
     */
    public function removeTranslation(TranslationInterface $translation): void;

    /**
     * Set current locale.
     *
     * @param string $locale
     */
    public function setCurrentLocale(string $locale): void;

    /**
     * Set fallback locale.
     *
     * @param string $locale
     */
    public function setFallbackLocale(string $locale): void;
}
