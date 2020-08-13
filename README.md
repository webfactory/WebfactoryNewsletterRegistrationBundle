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
- Highly customizable


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
