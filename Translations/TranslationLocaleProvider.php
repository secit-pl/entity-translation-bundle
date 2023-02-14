<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Translations;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TranslationLocaleProvider.
 *
 * @author Tomasz Gemza
 */
class TranslationLocaleProvider
{
    private ParameterBagInterface $parameterBag;
    private RequestStack $requestStack;

    /**
     * TranslationLocaleProvider constructor.
     *
     * @param ParameterBagInterface $parameterBag
     * @param RequestStack $requestStack
     */
    public function __construct(ParameterBagInterface $parameterBag, RequestStack $requestStack)
    {
        $this->parameterBag = $parameterBag;
        $this->requestStack = $requestStack;
    }

    /**
     * Get defined locales codes.
     *
     * @return array
     */
    public function getDefinedLocalesCodes(): array
    {
        return $this->parameterBag->getParameter('secit.entity_translation.locales.defined');
    }

    /**
     * Get default locale code.
     *
     * @return string
     */
    public function getDefaultLocaleCode(): string
    {
        return $this->parameterBag->getParameter('secit.entity_translation.locales.default');
    }

    /**
     * Get default locale code.
     *
     * @return string
     */
    public function getCurrentRequestLocale(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest) {
            $locale = $currentRequest->getLocale();
            if (in_array($locale, $this->getDefinedLocalesCodes())) {
                return $locale;
            }
        }

        return $this->getDefaultLocaleCode();
    }

    /**
     * Has multiple locales codes?
     *
     * @return bool
     */
    public function hasMultipleLocalesCodes(): bool
    {
        return count($this->getDefinedLocalesCodes()) > 1;
    }
}
