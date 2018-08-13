<?php

declare(strict_types=1);

namespace SecIT\EntityTranslationBundle\EventSubscriber;

use SecIT\EntityTranslationBundle\Translations\TranslatableInterface;
use SecIT\EntityTranslationBundle\Translations\TranslationInterface;
use SecIT\EntityTranslationBundle\Translations\TranslationLocaleProvider;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class TranslatableSubscriber.
 *
 * Based on Sylius translations engine.
 *
 * @author Tomasz Gemeza
 *
 * @see https://github.com/Sylius/Sylius/blob/master/src/Sylius/Bundle/ResourceBundle/EventListener/ORMTranslatableListener.php
 */
class TranslatableSubscriber implements EventSubscriber
{
    private const TRANSLATABLE_ENTITY_CLASS_NAME = 'Translation';

    /**
     * @var TranslationLocaleProvider
     */
    private $translationLocaleProvider;

    /**
     * TranslatableSubscriber constructor.
     *
     * @param TranslationLocaleProvider $translationLocaleProvider
     */
    public function __construct(TranslationLocaleProvider $translationLocaleProvider)
    {
        $this->translationLocaleProvider = $translationLocaleProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
            Events::prePersist,
        ];
    }

    /**
     * Add mapping to translatable entities.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
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
     * Post load.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof TranslatableInterface) {
            $entity->setCurrentLocale($this->translationLocaleProvider->getCurrentRequestLocale());
            $entity->setFallbackLocale($this->translationLocaleProvider->getDefaultLocaleCode());
        }
    }

    /**
     * Pre persist.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->postLoad($args);
    }

    /**
     * Add mapping data to a translatable entity.
     *
     * @param ClassMetadata $metadata
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
     * @param ClassMetadata $metadata
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
     *
     * @param ClassMetadata $metadata
     * @param array         $columns
     *
     * @return bool
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
