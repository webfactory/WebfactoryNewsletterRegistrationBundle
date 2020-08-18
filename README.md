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
 * @ORM\Entity(repositoryClass="\AppBundle\Entity\RecipientRepository")
 */
class RecipientRepository extends \Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepository
{
}
```

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\AppBundle\Entity\NewsletterRepository")
 * @ORM\Table("wfd_newsletterNewsletter")
 */
class Newsletter extends \Webfactory\NewsletterRegistrationBundle\Entity\Newsletter
{
}
```

```php
<?php

namespace AppBundle\Entity;

class NewsletterRepository extends \Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepository
{
}
```

Configure Doctrine's interface mapping with your actual entity classes:

```yml
// config.yml

doctrine:
    orm:
        resolve_target_entities:
            \Webfactory\NewsletterRegistrationBundle\Entity\NewsletterInterface: '\AppBundle\Entity\Newsletter'
```

And update your database schema, e.g. with a migration.

Configure the sender of opt in emails: 

```config.yml
parameters:
  webfactory.newsletter_registration.opt_in_sender_address: 'optin@jugendfuereuropa.de
```

The abstract RegistrationController gets some Interfaces injected in its constructor. Configure your controller
accordingly or if your use auto wiring, set aliases for the interface named services:  

```yaml
// src/services.yml

services:
  AppBundle\Newsletter\Controller:
    tags: ['controller.service_arguments']

  AppBundle\Newsletter\Entity\NewsletterRepository:
    factory:
      - '@doctrine.orm.entity_manager'
      - 'getRepository'
    arguments:
      - 'AppBundle\Entity\Newsletter'

  Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface:
    alias: 'AppBundle\Newsletter\Entity\NewsletterRepository'

  AppBundle\Newsletter\Entity\PendingOptInRepository:
    factory:
      - '@doctrine.orm.entity_manager'
      - 'getRepository'
    arguments:
      - 'AppBundle\Entity\PendingOptIn'

  Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface:
    alias: 'AppBundle\Newsletter\Entity\PendingOptInRepository'

  AppBundle\Newsletter\Entity\RecipientRepository:
    factory:
      - '@doctrine.orm.entity_manager'
      - 'getRepository'
    arguments:
      - 'AppBundle\Entity\Recipient'

  Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface:
    alias: 'AppBundle\Newsletter\Entity\RecipientRepository'
```
