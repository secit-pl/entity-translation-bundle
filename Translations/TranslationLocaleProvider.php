<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Translations;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TranslationLocaleProvider.
 *
 * @author Tomasz Gemza
 */
class TranslationLocaleProvider
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * Get defined locales codes.
     */
    public function getDefinedLocalesCodes(): array
    {
        return $this->parameterBag->get('secit.entity_translation.locales.defined');
    }

    /**
     * Get default locale code.
     */
    public function getDefaultLocaleCode(): string
    {
        return $this->parameterBag->get('secit.entity_translation.locales.default');
    }

    /**
     * Get default locale code.
     */
    public function getCurrentRequestLocale(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest) {
            $locale = $currentRequest->getLocale();
            if (in_array($locale, $this->getDefinedLocalesCodes(), true)) {
                return $locale;
            }
        }

        return $this->getDefaultLocaleCode();
    }

    /**
     * Has multiple locales codes?
     */
    public function hasMultipleLocalesCodes(): bool
    {
        return count($this->getDefinedLocalesCodes()) > 1;
    }
}
