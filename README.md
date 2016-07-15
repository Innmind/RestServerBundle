# REST Server Bundle

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/RestServerBundle/build-status/develop) |

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6ba6ddc4-6dc5-4a33-9f5a-1d9129dabe76/big.png)](https://insight.sensiolabs.com/projects/6ba6ddc4-6dc5-4a33-9f5a-1d9129dabe76)

## Installation

Via composer:

```sh
composer require innmind/rest-server-bundle
```

Enable the bundle by adding the following line in your app/AppKernel.php of your project:

```php
// app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Innmind\RestBundle\InnmindRestBundle,
        );
        // ...
    }
    // ...
}
```

Then you need to specify the types you allow in the app, here's an example:

```yaml
innmind_rest_server:
    accept:
        json:
            priority: 10
            media_types:
                application/json: 0
        html:
            priority: 0
            media_types:
                text/html: 10
                application/xhtml+xml: 0
    content_type:
        json:
            priority: 0
            media_types:
                application/json: 0
```

Here you define you can expose your resources either in `json` or `html`. If the client accept any kind of content, it will automatically expose data as `json` as it has the highest priority. The client can use either `text/html` or `application/xhtml+xml` as media type in his `Accept` header in order for us to expose data as `html`.

We also describe the fact resources sent to our API must me in `json` only, and that the `Content-Type` header sent by the client must be `application/json` otherwise he will get an error.

In order to work properly, any media type here must have a corresponding [serializer encoder](https://github.com/symfony/serializer/blob/3.0/Encoder/EncoderInterface.php) (the `supportsEnconding` must check the `request_{media_type}` format, [example](https://github.com/Innmind/rest-server/blob/master/src/Serializer/Encoder/JsonEncoder.php)).

Then you need to activate the router by adding this configuration:

```yaml
# app/config/routing.yml
rest:
    type: innmind_rest
    resource: .
```

The last part of the configuration is to create a file named `rest.yml` in your bundle under the folder `Resources/config` that will contain the definition of your resources. Here's an extended example:

```yaml
blog:
    resources:
        blog:
            identity: uuid
            gateway: command
            properties:
                uuid:
                    type: string
                title:
                    type: string
                    access: [READ, CREATE, UPDATE]
                content:
                    type: string
                    access: [READ, CREATE, UPDATE]
                tags:
                    type: set
                    options:
                        inner: string
                author:
                    type: string # identifier of the author
    children:
        meta:
            resources:
                author:
                    identity: uuid
                    gateway: command
                    properties:
                        uuid:
                            type: string
                        name:
                            type: string
```

Now that all the configuration is done, you need to create a service implementing the interface [`GatewayInterface`](https://github.com/Innmind/rest-server/blob/master/src/GatewayInterface.php) and tag the service definition with `innmind_rest_server.gateway` along with an alias to your choosing; the alias is the one you'll use in the configuration of your resources as chown above (which in our case is `command`).

Such a service definition should look like this:

```yaml
services:
    my_gateway:
        class: AppBundle\Gateway\MyGateway
        tags:
            - { name: innmind_rest_server.gateway, alias: command }
```
