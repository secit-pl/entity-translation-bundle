# Entity Translation Bundle

Doctrine entity translations Symfony 4.x.

## Installation

From the command line run

```
$ composer require secit-pl/entity-translation-bundle
```

## Usage

#### Configuration

Open configuration file `./config/packages/entity_translation.yaml`. If file not exists create it.

The file content shoud looks somehting like this:

```yaml
entity_translation:
    locales:
        defined:
          - pl
          - en_GB
          
        default: '%kernel.default_locale%' # in this exampel equals `pl`

```

#### Entity

###### Key rules

* Translatable entity should implements `SecIT\EntityTranslationBundle\Translations\TranslatableInterface`
* Translation entity should be called `Translation` and be placed in namespace same as translatable class name.
  For example, if translatable entity is `App\Entity\Shop` the translation class should be `App\Entity\Shop\Translation`
* Translation should extends `SecIT\EntityTranslationBundle\Entity\AbstractTranslation`

###### Example

Let's say we have following entity and we want to translate the `name` and `description` fields.
Other field should not be translated.

./src/Entity/Shop.php

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Shop.
 *
 * @ORM\Table(name="shops")
 * @ORM\Entity
 */
class Shop
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $street;

    /**
     * Get id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param null|string $name
     *
     * @return Shop
     */
    public function setName(?string $name): Shop
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description.
     *
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param null|string $description
     *
     * @return Shop
     */
    public function setDescription(?string $description): Shop
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get city.
     * 
     * @return null|string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Set city.
     * 
     * @param null|string $city
     *
     * @return Shop
     */
    public function setCity(?string $city): Shop
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get street.
     * 
     * @return null|string
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * Set street.
     * 
     * @param null|string $street
     *
     * @return Shop
     */
    public function setStreet(?string $street): Shop
    {
        $this->street = $street;

        return $this;
    }
}

```

We need to split the file to two separeted files. One will contain the common part of each translation and one will contain the fields we want to translate.

First we need to create a Shop Translation entity. The entity should be placed in namespace `App\Entity\Shop`.  

./src/Entity/Shop/Translation.php

```php
<?php

declare(strict_types=1);

namespace App\Entity\Shop;

use App\Entity\Shop;
use Doctrine\ORM\Mapping as ORM;
use SecIT\EntityTranslationBundle\Entity\AbstractTranslation;

/**
 * Class Translation.
 *
 * @ORM\Table(name="shops_translations")
 * @ORM\Entity
 *
 * @method Shop getTranslatable()
 */
class Translation extends AbstractTranslation
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $description;

    /**
     * Get id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param null|string $name
     *
     * @return Translation
     */
    public function setName(?string $name): Translation
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description.
     *
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param null|string $description
     *
     * @return Translation
     */
    public function setDescription(?string $description): Translation
    {
        $this->description = $description;

        return $this;
    }
}

```

What has been done:
* We created a class `Translation` in namespace `App\Entity\Shop`
* The class extends `SecIT\EntityTranslationBundle\Entity\AbstractTranslation`
* We created a standard entity with `name` and `description` fields copied from original entity
* To improve type hinting we added `@method Shop getTranslatable()`

Next it's time to change the base entity.

./src/Entity/Shop.php

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Translation;
use Doctrine\ORM\Mapping as ORM;
use SecIT\EntityTranslationBundle\Translations\TranslatableInterface;
use SecIT\EntityTranslationBundle\Translations\TranslatableTrait;

/**
 * Class Shop.
 *
 * @ORM\Table(name="shops")
 * @ORM\Entity
 *
 * @method Translation getTranslation(?string $locale)
 * @method Collection|Translation[] getTranslations
 */
class Shop implements TranslatableInterface
{
    use TranslatableTrait  {
        __construct as private initializeTranslationsCollection;
    }
    
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose
     */
    private $street;

    /**
     * Shop constructor.
     */
    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }
    
    /**
     * Get id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @param null|string locale
     *
     * @return null|string
     */
    public function getName(?string $locale = null): ?string
    {
        return $this->getTranslation($locale)->getName();
    }

    /**
     * Get description.
     *
     * @param null|string locale
     *
     * @return null|string
     */
    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslation($locale)->getDescription();
    }

    /**
     * Get city.
     * 
     * @return null|string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Set city.
     * 
     * @param null|string $city
     *
     * @return Shop
     */
    public function setCity(?string $city): Shop
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get street.
     * 
     * @return null|string
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * Set street.
     * 
     * @param null|string $street
     *
     * @return Shop
     */
    public function setStreet(?string $street): Shop
    {
        $this->street = $street;

        return $this;
    }
}

```

What has been done:
* We added class implements `SecIT\EntityTranslationBundle\Translations\TranslatableInterface`
* We used default implementation of implemented interface using `SecIT\EntityTranslationBundle\Translations\TranslatableTrait`
* We added constructor to initialize translations collection
* We removed `name` and `description` setters
* `getName` and `getDescription` was changed to have easier way to get translated values and to be more backward compatible
* Type hinting was added

Now we need to update the database schema by `php bin/console doctrine:schema:update --force` and that's it. Now our entity is translatable.

**Remember, if you had a data in database you should move it manually to the new database schema!**

#### Usage

```php
<?php

// creating entity
$shop = new Shop();
$shop->setCity('city')
    ->setStreet('some street 1');

// adding polish translation
$shop->getTranslation('pl')
    ->setName('Nazwa')
    ->setDescription('Opis...');

// adding english translation
$shop->getTranslation('en_GB')
    ->setName('Name')
    ->setDescription('Description...');

$doctrine->getManager()->persist($shop);
$doctrine->getManager()->flush();

// fetching current locale translation
// let's say the default locale is en_GB
$name = $shop->getName(); // Name

// fetching defined locale translation
$name = $shop->getName('pl'); // Nazwa

// change the current locale
$shop->setCurrentLocale('pl');
$name = $shop->getName(); // Nazwa

```

#### Using translatable entity in forms

You need to create two classes. One for Translatable entity, and one for translation.

./src/Form/ShopType.php

```php
<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Shop;
use App\Form\Shop\TranslationType;
use SecIT\EntityTranslationBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShopType.
 */
class ShopType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => TranslationType::class,
            ])
            ->add('city')
            ->add('street')
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
        ]);
    }
}

```

./src/Form/Shop/TranslationType.php

```php
<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Shop\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TranslationType.
 */
class TranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Translation::class,
        ]);
    }
}

```

Now you can use `App\Form\ShopType` like a normal Symfony form. 
Translations will be handled automatically.
