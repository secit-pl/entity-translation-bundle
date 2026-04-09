<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Translations;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;

/**
 * Interface TranslatableTrait.
 *
 * @author Tomasz Gemza
 */
trait TranslatableTrait
{
    /**
     * Dynamically mapped in TranslatableSubscriber.
     *
     * @var ArrayCollection|PersistentCollection|TranslationInterface[]
     *
     * @see \SecIT\EntityTranslationBundle\EventSubscriber\TranslatableSubscriber
     */
    protected ArrayCollection $translations;

    /**
     * @var array|TranslationInterface[]
     */
    protected array $translationsCache = [];

    protected ?string $currentLocale = null;

    /**
     * @var string|null
     */
    protected ?string $fallbackLocale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get translations.
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * Get translation.
     */
    public function getTranslation(?string $locale = null): TranslationInterface
    {
        $locale = $locale ?: $this->currentLocale;
        if (null === $locale) {
            throw new \RuntimeException('No locale has been set and current locale is undefined.');
        }

        if (isset($this->translationsCache[$locale])) {
            return $this->translationsCache[$locale];
        }

        $translation = $this->translations->get($locale);
        if (null !== $translation) {
            $this->translationsCache[$locale] = $translation;

            return $translation;
        }

        if ($locale !== $this->fallbackLocale) {
            if (isset($this->translationsCache[$this->fallbackLocale])) {
                return $this->translationsCache[$this->fallbackLocale];
            }

            $fallbackTranslation = $this->translations->get($this->fallbackLocale);
            if (null !== $fallbackTranslation) {
                $this->translationsCache[$this->fallbackLocale] = $fallbackTranslation;

                return $fallbackTranslation;
            }
        }

        $translation = $this->createTranslation();
        $translation->setLocale($locale);

        $this->addTranslation($translation);

        $this->translationsCache[$locale] = $translation;

        return $translation;
    }

    /**
     * Check if translatable element has translation.
     */
    public function hasTranslation(TranslationInterface $translation): bool
    {
        return isset($this->translationsCache[$translation->getLocale()]) || $this->translations->containsKey($translation->getLocale());
    }

    /**
     * Add translation.
     */
    public function addTranslation(TranslationInterface $translation): void
    {
        if (!$this->hasTranslation($translation)) {
            $this->translationsCache[$translation->getLocale()] = $translation;

            $this->translations->set($translation->getLocale(), $translation);
            $translation->setTranslatable($this);
        }
    }

    /**
     * Remove translation.
     */
    public function removeTranslation(TranslationInterface $translation): void
    {
        if ($this->translations->removeElement($translation)) {
            unset($this->translationsCache[$translation->getLocale()]);

            $translation->setTranslatable(null);
        }
    }

    /**
     * Set current locale.
     */
    public function setCurrentLocale(string $currentLocale): void
    {
        $this->currentLocale = $currentLocale;
    }

    /**
     * Get fallback locale.
     */
    public function getFallbackLocale(): ?string
    {
        return $this->fallbackLocale;
    }

    /**
     * Set fallback locale.
     */
    public function setFallbackLocale(string $fallbackLocale): void
    {
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * Create resource translation model.
     */
    protected function createTranslation(): TranslationInterface
    {
        $class = get_class($this).'\\Translation';

        return new $class();
    }
}
