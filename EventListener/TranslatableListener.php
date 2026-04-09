<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\EventListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use SecIT\EntityTranslationBundle\Translations\TranslatableInterface;
use SecIT\EntityTranslationBundle\Translations\TranslationInterface;
use SecIT\EntityTranslationBundle\Translations\TranslationLocaleProvider;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class TranslatableListener.
 *
 * Based on Sylius translations engine.
 *
 * @author Tomasz Gemeza
 *
 * @see https://github.com/Sylius/Sylius/blob/master/src/Sylius/Bundle/ResourceBundle/EventListener/ORMTranslatableListener.php
 */
class TranslatableListener
{
    private const TRANSLATABLE_ENTITY_CLASS_NAME = 'Translation';

    public function __construct(
        private readonly TranslationLocaleProvider $translationLocaleProvider,
    ) {
    }

    /**
     * Add mapping to translatable entities.
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $reflection = $classMetadata->reflClass;
        if (!$reflection || $reflection->isAbstract()) {
            return;
        }

        if ($reflection->implementsInterface(TranslatableInterface::class)) {
            $this->mapTranslatable($classMetadata);
        }

        if ($reflection->implementsInterface(TranslationInterface::class)) {
            $this->mapTranslation($classMetadata);
        }
    }

    /**
     * Post load event handling.
     */
    public function postLoad(PostLoadEventArgs $args): void
    {
        $this->processEvent($args);
    }

    /**
     * Pre persist event handling.
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->processEvent($args);
    }

    /**
     * Events handling logic is common for both post load and pre persist events.
     */
    private function processEvent(PostLoadEventArgs|PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof TranslatableInterface) {
            $entity->setCurrentLocale($this->translationLocaleProvider->getCurrentRequestLocale());
            $entity->setFallbackLocale($this->translationLocaleProvider->getDefaultLocaleCode());
        }
    }

    /**
     * Add mapping data to a translatable entity.
     */
    private function mapTranslatable(ClassMetadata $metadata): void
    {
        if (!$metadata->hasAssociation('translations')) {
            $metadata->mapOneToMany([
                'fieldName' => 'translations',
                'targetEntity' => $metadata->name.'\\'.self::TRANSLATABLE_ENTITY_CLASS_NAME,
                'mappedBy' => 'translatable',
                'fetch' => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'indexBy' => 'locale',
                'cascade' => ['persist', 'merge', 'remove'],
                'orphanRemoval' => true,
            ]);
        }
    }

    /**
     * Add mapping data to a translation entity.
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    private function mapTranslation(ClassMetadata $metadata): void
    {
        if (!$metadata->hasAssociation('translatable')) {
            $metadata->mapManyToOne([
                'fieldName' => 'translatable',
                'targetEntity' => substr($metadata->name, 0, strlen(self::TRANSLATABLE_ENTITY_CLASS_NAME) * -1 - 1),
                'inversedBy' => 'translations',
                'joinColumns' => [[
                    'name' => 'translatable_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                    'nullable' => false,
                ]],
            ]);
        }

        if (!$metadata->hasField('locale')) {
            $metadata->mapField([
                'fieldName' => 'locale',
                'type' => 'string',
                'nullable' => false,
            ]);
        }

        $columns = [
            $metadata->getSingleAssociationJoinColumnName('translatable'),
            'locale',
        ];

        if (!$this->hasUniqueConstraint($metadata, $columns)) {
            $constraints = $metadata->table['uniqueConstraints'] ?? [];
            $constraints[$metadata->getTableName().'_uniq_trans'] = [
                'columns' => $columns,
            ];

            $metadata->setPrimaryTable([
                'uniqueConstraints' => $constraints,
            ]);
        }
    }

    /**
     * Check if a unique constraint has been defined.
     */
    private function hasUniqueConstraint(ClassMetadata $metadata, array $columns): bool
    {
        if (!isset($metadata->table['uniqueConstraints'])) {
            return false;
        }

        foreach ($metadata->table['uniqueConstraints'] as $constraint) {
            if (!array_diff($constraint['columns'], $columns)) {
                return true;
            }
        }

        return false;
    }
}
