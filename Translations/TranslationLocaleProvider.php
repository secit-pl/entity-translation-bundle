<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\Translations;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TranslationLocaleProvider.
 *
 * @author Tomasz Gemza
 */
class TranslationLocaleProvider
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * TranslationLocaleProvider constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get defined locales codes.
     *
     * @return array
     */
    public function getDefinedLocalesCodes(): array
    {
        return $this->container->getParameter('secit.entity_translation.locales.defined');
    }

    /**
     * Get default locale code.
     *
     * @return string
     */
    public function getDefaultLocaleCode(): string
    {
        return $this->container->getParameter('secit.entity_translation.locales.default');
    }

    /**
     * Get default locale code.
     *
     * @return string
     */
    public function getCurrentRequestLocale(): string
    {
        $currentRequest = $this->container->get('request_stack')->getCurrentRequest();
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
