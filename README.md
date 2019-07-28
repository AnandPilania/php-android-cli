# php-android-cli
Create Android-Studio gradle project  from CLI with PHP (Symfony Console)...

Syntax: `php index.php create [options] [--] <project> <pkg>`

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
