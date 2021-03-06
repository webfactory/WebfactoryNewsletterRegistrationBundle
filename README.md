WebfactoryNewsletterRegistrationBundle
======================================

![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Tests/badge.svg)
![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Dependencies/badge.svg)
![](https://github.com/webfactory/WebfactoryNewsletterRegistrationBundle/workflows/Coding%20Standards/badge.svg)

This Symfony bundle features a newsletter registration template with attention to data avoidance for privacy
protection:

- Sign up with email address only (which is also a low barrier for a better interaction rate)
- No personal data (like the email address) is saved until the newsletter recipient verifies their email address (double opt in)
- Pending opt in processes get deleted after a configurable amount of time (default: 72 hours)
- Intentionally vague messages to prevent leaking user meta information (e.g. if some email address is registered)

To reduce the amount of unwanted emails, the following ideas are implemented:

- The registration form has a simple honeypot field
- Opt in emails contain no data entered in the registration form to make them unattractive for spammers
- Opt in emails can be sent only once in a configurable time interval (default: 1 hour)
- Opt in emails contain a "block this email address" link (default: for 30 days)

Finally, the bundle tries to be developer friendly:

- Registration can be embedded as a page on it's own as well as a partial view
- Depending on the number of different newsletters, the registration and edit forms feature a newsletter selection or
  no disturbing element (a checkbox for a single newsletter would be silly) 
- It's highly customizable due to small interfaces, Doctrine interface mapping and service replacements


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

Configure the bundle:

```yaml
// config.yml

parameters:
  webfactory.newsletter_registration.email_sender_address: 'newsletter@example.com'
  webfactory.newsletter_registration.secret: 'your-secret' # do not use Symfony's %secret%!
  webfactory.newsletter_registration.time_limit_for_opt_in_in_hours: 72 # default value
  webfactory.newsletter_registration.minimal_interval_between_op_in_emails_in_hours: 1 # default value
  webfactory.newsletter_registration.block_email_address_duration_in_days: 30 # default value
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


Delete outdated data
--------------------

    bin/console newsletter-registration:delete-outdated-pending-opt-ins
    bin/console newsletter-registration:delete-outdated-blocked-email-addresses


Customization
-------------

### Views

Use [the regular symfony mechanism for overriding templates](https://symfony.com/doc/4.4/bundles/override.html#templates).

Start with:

    mkdir src/Resources/WebfactoryNewsletterRegistrationBundle -p
    cp -r vendor/webfactory/newsletter-registration-bundle/src/Resources/views src/Resources/WebfactoryNewsletterRegistrationBundle 


### Translations

Use the [translation component's overwrite mechanism](https://symfony.com/doc/4.4/translation.html#translation-resource-file-names-and-locations).

If you add new languages or fix mistakes, please consider contributing via pull request. 


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
