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
        return $this->container->getParameter('allowed_locales');
    }

    /**
     * get default locale code.
     *
     * @return string
     */
    public function getDefaultLocaleCode(): string
    {
        return $this->container->getParameter('kernel.default_locale');
    }
}
