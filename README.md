# Symfony Redirect Bundle

Configure redirections after a migration or structural changes to your app/website.
It catches exception events, if they are of type `NotFoundHttpException` it will look for a configured rule and return a `RedirectResponse` response to redirect the user.

Works for Symfony 2.8 or 3.x.

## Installation

Install via Composer

```bash
$ composer require autologic-web/redirect-bundle
```

Include the bundle in `AppKernel.php`

```php
# app/AppKernel.php

/**
 * Class AppKernel
 */
class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $bundles = [
            // All your bundles
            
            new Autologic\Bundle\RedirectBundle\AutologicRedirectBundle(),
        ];
        
        return $bundles;
    }
}
```

## Configuration

Use regular expressions to match routes and set the redirect URL and optionally, the status code (default is 301) for the redirection and whether the original URI should be appended to the redirection.

```yaml
autologic_redirect:
  rules:
    # basic usage
    - { pattern: '/.*old-route/', redirect: 'domain.com/new-route' }
    # custom status code
    - { pattern: '/.*old-route/', redirect: 'domain.com/new-route', status: 302 }
    # forwarding: this will redirect to domain.com/new-route/old-route
    - { pattern: '/.*old-route/', redirect: 'domain.com/new-route', forwarding: true }
    # priority: this first rule will match first when a user visits /old-route/sub-route, the second acting as a fallback
    - { pattern: '/.*old-route\/sub-route', redirect: 'domain.com/new-route/sub-route' }
    - { pattern: '/.*old-route/', redirect: 'domain.com/new-route' }
```
