# PHP Android CLI

[![Hits](https://hits.seeyoufarm.com/api/count/incr/badge.svg?url=https%3A%2F%2Fgithub.com%2FAnandPilania%2Fphp-android-cli&count_bg=%23FF3863&title_bg=%232C3E50&title=hits&edge_flat=false)](https://hits.seeyoufarm.com)

PHP Android CLI create/generate Scaffold Android-Studio Gradle project with:

- `Java` or `Kotlin` language,
- `legacy` or standard package
- Modules (application/library)
- Variants with Dimensions
- Scaffold project & application/library level `build.gradle` with package name & `dimensions` & `variants`
- Manage `settings.gradle`
- Generate `manifest` file & `res` with default `icon`, `color`, `style` & `values`
- ...

# New Features!

- Language select `java` or `kotlin`
- Version `legacy` or standard

# Removed!

- `androidX` selection,
- `jetifier` selection,

You can also:

- set `targetSdk`
- set `buildToolsVersion`
- set `minSdk` & `maxSdk`
- `JAVA` or `KOTLIN`
- `legacy`

### Tech

PHP Android CLI uses:

- [Symfony Console](https://symfony.com/console) - ...

And of course `PHP Android CLI` itself is open source with a [public repository](https://github.com/AnandPilania/php-android-cli) on GitHub.

### Installation

PHP Android CLI requires [PHP](https://php.net/) v5+ to run.

Just download the [`phpandroid`](https://github.com/AnandPilania/php-android-cli/releases/latest) and start scaffolding.

```sh
$ phpandroid create <PROJECT_NAME> <PACKAGE> [OPTIONS]
```

## USAGE

### Basic use

Create `HelloWorld` project with `com.example.helloworld` package name:

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

## Options

### Default

PHP Android CLI is currently using default values for latest Android. These are:

| OPTIONS              | Usage                   | DEFAULT  |
| -------------------- | ----------------------- | -------- |
| `--type`/`-t`        | set `type`              | `kotlin` |
| `--legacy`/`-l`      | set `legacy`            | `false`  |
| `--compileSdk`/`-cs` | set `targetSdk`         | 31       |
| `--buildTools`/`-bt` | set `buildToolsVersion` | 31.0.0   |
| `--minSdk`/`-ms`     | set `minSdk`            | 21       |
| `--targetSdk`/`-ts`  | set `maxSdk`            | 31       |

Use `--force` to re-write existing project.

### Todos

- Create/Scaffold `activity`
- Create/Scaffold `variants` source
- ...

## License

MIT

**Free Software, Hell Yeah!**
