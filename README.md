WebfactoryNewsletterRegistrationBundle
======================================

![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Tests/badge.svg)
![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Dependencies/badge.svg)
![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Coding%20Standards/badge.svg)

This Symfony bundle features a newsletter registration template with attention to data avoidance for privacy
protection and a smooth user journey:

- Sign up with email address only
- No data is saved until the newsletter recipient verifies their email address (double opt in)
- Additional user data can be provided after double opt in (planned)
- Supports newsletter categories
- Highly customizable due to small interfaces, Doctrine interface mapping (e.g. there are some webfactory specific names
  you might want to change) and service replacements.


Installation
------------

    composer req webfactory/newsletter-registration-bundle
    
activate in `src/bundles.php`:

```php
<?php

return [
    // ...
    Webfactory\NewsletterRegistrationBundle\WebfactoryNewsletterRegistrationBundle::class => ['all' => true],
];
```


Usage
-----

Implement all `src/Entity/*Interface.php` in your project. The easiest way is to extend the corresponding abstract
class, add class level Doctrine ORM annotations (find template for them in the abstract classes) and customize them to
your liking.

Example:

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="wfd_newsletterRecipient", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="email_unique",columns={"email"}),
 *     @ORM\UniqueConstraint(name="uuid_unique",columns={"uuid"}),
 * })
 */
class Recipient extends \Webfactory\NewsletterRegistrationBundle\Entity\Recipient
{
}
```

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table("wfd_newsletterCategory")
 */
class Category extends \Webfactory\NewsletterRegistrationBundle\Entity\Category
{
}
```

Configure Doctrine's interface mapping with your actual entity classes:

```yml
// config.yml

doctrine:
    orm:
        resolve_target_entities:
            \Webfactory\NewsletterRegistrationBundle\Entity\CategoryInterface: '\AppBundle\Entity\Category'
```
