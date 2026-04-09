# Entity Translation Bundle

Doctrine entity translations for Symfony.

## Compatibility matrix


| Bundle version | Maintained | Symfony versions | Min. PHP version |
|----------------|------------|------------------|------------------|
| 2.x            | Yes        | 7.0 to 8.x       | 8.1.0            |
| 1.6            | No         | 4.0 to 6.4       | 7.1.0            |

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
          
        default: '%kernel.default_locale%' # in this example equals `pl`

```

#### Entity

###### Key rules

* Translatable entity should implements `SecIT\EntityTranslationBundle\Translations\TranslatableInterface`
* Translation entity should be called `Translation` and be placed in namespace same as translatable class name.
  For example, if translatable entity is `App\Entity\Shop` the translation class should be `App\Entity\Shop\Translation`
* Translation should extends `SecIT\EntityTranslationBundle\Entity\AbstractTranslation`

###### Example

Let's say we have the following entity, and we want to translate the `name` and `description` fields.
Other field should not be translated.

./src/Entity/Shop.php

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shops')]
class Shop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $name = null;

    #[ORM\Column]
    private ?string $description = null;

    #[ORM\Column]
    private ?string $city = null;

    #[ORM\Column]
    private ?string $street = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }
}

```

We need to split the file to two separated files. One will contain the common part of each translation and one will contain the fields we want to translate.

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
 * @method Shop getTranslatable()
 */
#[ORM\Entity]
#[ORM\Table(name: 'shops_translations')]
class Translation extends AbstractTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $name = null;

    #[ORM\Column]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
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
 * @method Translation getTranslation(?string $locale)
 * @method Collection|Translation[] getTranslations
 */
#[ORM\Entity]
#[ORM\Table(name: 'shops')]
class Shop implements TranslatableInterface
{
    use TranslatableTrait  {
        __construct as private initializeTranslationsCollection;
    }
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $city = null;

    #[ORM\Column]
    private ?string $street = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(?string $locale = null): ?string
    {
        return $this->getTranslation($locale)->getName();
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslation($locale)->getDescription();
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
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

// adding Polish translation
$shop->getTranslation('pl')
    ->setName('Nazwa')
    ->setDescription('Opis...');

// adding English translation
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

class ShopType extends AbstractType
{
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

class TranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description');
    }

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
