# Symfony Redirect Bundle

[![Build Status](https://travis-ci.com/autologic-web/redirect-bundle.svg?token=u16nzQx7npX8bQUAmcyy&branch=master)](https://travis-ci.com/autologic-web/redirect-bundle) [![StyleCI](https://styleci.io/repos/106713467/shield?branch=master)](https://styleci.io/repos/106713467)

Configure redirections after a migration or structural changes to your app/website. It helps with SEO rankings and user experience.
It catches exception events, if they are of type `NotFoundHttpException` it will look for a configured rule and return a `RedirectResponse` response to redirect the user. In doing so, it avoids complicated `.htaccess` or vhost configurations but can be used in conjunction with them - picking up redirects only after a page has not been found.

Works for Symfony 2.8 or 3.x with PHP > 7.1

It's been designed to be as unobtrusive as possible since the need to do this sort of thing is often temporary - Google recommends leaving them in place for a year. Just include the bundle and add a block of configuration for your redirect rules.

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

Use regular expressions to match routes and set the redirect URL and optionally, the status code (default is 301) for the redirection and whether the original URI should be appended to the redirection (useful in the case that other services/applications have their own redirect logic in place or route structure is the same on a different domain).

```yaml
# app/config.yml

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

## Logging 
To enable logging of unmatched 404 errors, just inject a logger into the listener service in your services.yml:

```yaml
# app/services.yml

services:
  autologic_redirect.event.redirect_listener:
    class: Autologic\Bundle\RedirectBundle\Event\RedirectListener
    arguments:
      - '@autologic_redirect.service.redirect_service'
      - '@logger'
    tags:
      - { name: kernel.event_listener, event: kernel.exception }
```
This will log at `notice` level to help sniff out 404s that don't have any redirection rules in place.
