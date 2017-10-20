# Symfony Redirect Bundle

[![Build Status](https://travis-ci.org/autologic-web/redirect-bundle.svg?branch=master)](https://travis-ci.org/autologic-web/redirect-bundle) [![StyleCI](https://styleci.io/repos/106713467/shield?branch=master)](https://styleci.io/repos/106713467)

Configure redirections after a migration or structural changes to your app/website.

It catches exception events, if they are of type `NotFoundHttpException` it will look for a configured rule and return a `RedirectResponse` response to redirect the user.

Works for Symfony ^2.7 or ^3.0 with PHP ^5.6 or ^7.0

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

### Basic Usage
```yaml
# app/config.yml

autologic_redirect:
  rules:
    - { pattern: '/old-route/', redirect: 'domain.com/new-route' }
```

### `pattern` (string, required)
Use regular expressions to match the full URI being requested. The service catches 404 exceptions, uses `preg_match` to find a rule matching the missing page's URI before throwing the 404 in the event it cannot match.

### `redirect` (string, required)
The fully qualified redirect URI. The bundle will set the protocol (http/https) based on the incoming original request so it ports from dev to prod easily.

### `status` (int, optional)
Set the status code (__default: 301__) for the redirection. Tip: use 302 while debugging to avoid 301 permanent redirects from being cached in the browser.

### `forwarding` (bool, optional)
Append the original route to the redirection (__default: false__). Useful in the case that other services/applications have their own redirect logic in place or route structure is the same on a different domain or path.

### `absolute` (bool, optional)
Force absolute or relative redirects (__default: null/auto__). If left unset, it will detect a hostname in the redirect and either use the original request host if the redirect does not contain a host or use the redirect verbatim if it does.

### `protocol` (string, optional)
Force the redirect protocol (__default: null/auto__). If left unset, it will detect the protocol from the original request and use that.

### Other Examples
```yaml
# app/config.yml

autologic_redirect:
  rules:
    # custom status code
    - { pattern: '/old-route/', redirect: 'domain.com/new-route', status: 302 }
    # forwarding: this will redirect to domain.com/new-route/old-route
    - { pattern: '/old-route/', redirect: 'domain.com/new-route', forwarding: true }
    # absolute: will force relative or absolute redirects
    # if false it will redirect to the route on the current host
    - { pattern: '/old-route/', redirect: '/new-route', absolute: false }
    # protocol: will force the protocol
    - { pattern: '/old-route/', redirect: '/new-route', protocol: 'ftp://' }
    # priority: this first rule will match first when a user visits /old-route/sub-route, the second acting as a fallback
    - { pattern: '/.*old-route\/sub-route', redirect: 'domain.com/new-route/sub-route' }
    - { pattern: '/.*old-route/', redirect: 'domain.com/new-route' }
    # match subdomains and more complex patterns and use parameters
    - { pattern: '/au\..+?\.[^\/]+.*blog\/old-australian-blog-post-on-any-domain-of-subdomain/',
        redirect: 'au.%base_domain%/news/new-australian-news-article' }
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
