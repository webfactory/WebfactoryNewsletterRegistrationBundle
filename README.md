WebfactoryNewsletterRegistrationBundle
======================================

![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Tests/badge.svg)
![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Dependencies/badge.svg)
![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Coding%20Standards/badge.svg)

This Symfony bundle features a newsletter registration template with attention to data avoidance for privacy
protection and a smooth user journey:

- Sign up with email address only
- No personal data is saved until the newsletter recipient verifies their email address (double opt in)
- Additional user data can be provided after double opt in (planned)
- Supports zero, one or many newsletters
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

Implement all `src/Entity/*Interface.php` in your project. The easiest way, if you don't mind the biased namespaces, is
to copy the templates:

    mkdir src/AppBundle/Newsletter
    cp vendor/webfactory/newsletter-registration-bundle/app-class-templates/* src/AppBundle/Newsletter/*

If you want to implement the interfaces by yourself, you could extend the corresponding abstract classes like in the
templates above and add class level Doctrine ORM annotations (find template for them in the abstract classes). For
customizing, see the "Customizing" section below.

In either case, configure Doctrine's interface mapping to deal with your custom entity class:

```yaml
// config.yml

doctrine:
    orm:
        resolve_target_entities:
            \Webfactory\NewsletterRegistrationBundle\Entity\NewsletterInterface: '\AppBundle\Entity\Newsletter'
```

Side node: The templates and example above assume that you want to keep your Newsletter classes inside a Newsletter
directory in your AppBundle. If you choose to do so, you might need to configure Doctrine to load the entities: 

```yaml
// config.yml

doctrine:
    orm:
        entity_managers:
            default:
                mappings:
                    NewsletterRegistrationBundle:
                        type: annotation
                        prefix: AppBundle\Newsletter\Entity\
                        dir: "%kernel.root_dir%/AppBundle/Newsletter/Entity/"
                        is_bundle: false
```

Update your database schema, e.g. with a migration.

Configure the sender of opt in emails: 

```yaml
// config.yml

parameters:
  webfactory.newsletter_registration.opt_in_sender_address: 'newsletter-registration@example.com'
```

Include the RegistrationController in your routing:

```yaml
// routing.yml

newsletter:
    prefix: /newsletter
    type: annotation
    resource: '@WebfactoryNewsletterRegistrationBundle/Controller/RegistrationController.php'
```
 
The RegistrationController gets some Interfaces injected in its constructor. Alias these interfaces with your own
implementations: 

```yaml
// src/services.yml

services:
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

Customization
-------------

### Views

Use [the regular symfony mechanism for overriding templates](https://symfony.com/doc/4.4/bundles/override.html#templates).

### Adding fields

- Extend the StartRegistration Type with a [Form Type Extension](https://symfony.com/doc/4.4/form/create_form_type_extension.html).
- Add the new fields to your entities (`AppBundle\Entity\PendingOptIn` and `AppBundle\Entity\Recipient` in the example
  above). Maybe you want to extends their respective Repositories, too.
- Implement `Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface` and `Webfactory\NewsletterRegistrationBundle\Entity\RecpientFactoryInterface`,
  as they are responsible for creating your entities from the corresponding form data. Alias the interfaces to your
  implementations, e.g.
  ```yaml
  // services.yml
  services:
    Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactoryInterface:
        alias: 'App\Newsletter\Entity\PendingOptInFactory'
  ```

### Logic

If you can pin down your modifications to the `StartRegistration`, `ConfirmRegistration`, `EditRegistration` or
`DeleteRegistration` tasks, you are probably better off implementing your own versions of the respective interface
(maybe extending the task class) and aliasing the interface service to them.

For greater flexibility, you can replace the RegistrationController with your own, e.g.:

```php
<?php

namespace AppBundle\Newsletter;

class Controller extends \Webfactory\NewsletterRegistrationBundle\Controller\Controller
{
    // ...
}
```

Don't forget to configure your routing and services accordingly.
