# PHP Android CLI

PHP Android CLI create/generate Scaffold Android-Studio Gradle project with:

  - Modules (application/library)
  - Variants with Dimensions
  - ...

# New Features!

  - ...


You can also:
  - set `targetSdk`
  - set `buildToolsVersion`
  - set `minSdk` & `maxSdk`

### Tech

PHP Android CLI uses:

* [Symfony Console](https://symfony.com/console) - ...

And of course `PHP Android CLI` itself is open source with a [public repository][https://github.com/AnandPilania/php-android-cli] on GitHub.

### Installation

PHP Android CLI requires [PHP](https://php.net/) v5+ to run.

Just download the [`phpandroid`](https://github.com/AnandPilania/php-android-cli/releases/latest) and start scaffolding.

```sh
$ phpandroid create <PROJECT_NAME> <PACKAGE> [OPTIONS]
```

## USAGE
### Basic use

Create `HelloWorld` project with `org.example.helloworld` package name:
```sh
phpandroid create HelloWorld com.example.helloworld
```

### Create `Modules` along with `App`
Create `HelloWorld` project with `sdk` library & `admin` application
```sh
phpandroid create HelloWorld com.example.helloworld --modules=sdk:library,admin
```

### Create `productVariants`: `free` & `paid` variant
```sh
phpandroid create HelloWorld com.example.helloworld --variants=free:type,paid:type
```

here `type` is the `dimension`

## Default Options

PHP Android CLI is currently using default values for latest Android. These are:

| OPTIONS | Usage | DEFAULT |
| ------ | ------ | ------ |
| --targetSdk | set `targetSdk` | 29 |
| --buildTools | set `buildToolsVersion` | 29.0.1 |
| --minSdk | set `minSdk` | 16 |
| --maxSdk | set `maxSdk` | 29 |
| --androidX | Enable/Disable AndroidX | true |
| --jetifier | Enable/Disable Jetifier | true |

### Todos

 - Create/Scaffold `activity`
 - Create/Scaffold `variants` source
 - ...

License
----

MIT


**Free Software, Hell Yeah!**
