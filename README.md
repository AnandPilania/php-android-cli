# php-android-cli
Create/Scaffold Android-Studio gradle project with Modules & Flavors from CLI with PHP in seconds (Symfony Console)... [the ugly way... but working nice :)]

Syntax: `php index.php create [options] [--] <project> <pkg>`

Help: `php index.php help create`

Basic: `php index.php create HelloWorld com.example.helloworld`


-OPTIONS

With Variants: `php index.php create HelloWorld com.example.helloworld --flavors=free:type,paid:type`

With Modules: `php index.php create HelloWorld com.example.helloworld --modules=sdk:library,client`

Set targetSdk: `--targetSdk=29`

Set compileSdk: `--compileSdk=29`

Set minSdk: `--minSdk=16`

Set buildTools: `--buildTools="29.0.1"`

By default `androidX` is enabled, disable it: '--androidX=false'

By default `Jetifier` is enabled, disable it: '--jetifier=false'

Forcefully: `--force`
