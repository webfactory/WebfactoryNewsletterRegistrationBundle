# App namespace for automatic implementation detection tests

Some factories in this bundle try to detect implementation classes by filtering out classes in namespaces starting
with `Webfactory\NewsletterRegistrationBundle`. This is why we need this extra namespace, included separately in the
`composer.json`, to provide the test with implementations to find.

See `DetermineAppsSubclassTrait::getAppsSubclassOf()`.
