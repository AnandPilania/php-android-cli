<?php

namespace KSPEdu\PHPAndroidCli;

trait ChangableTrait
{

    protected function projectBuildGradle($isKotlin)
    {
        return "
// Top-level build file where you can add configuration options common to all sub-projects/modules.
buildscript {
    " . ($isKotlin ? 'ext.kotlin_version = "1.5.30"' : '') . "
    repositories {
        google()
        mavenCentral()
    }
    dependencies {
        classpath \"com.android.tools.build:gradle:4.2.0\"
        " . ($isKotlin ? 'classpath "org.jetbrains.kotlin:kotlin-gradle-plugin:$kotlin_version"' : '') . "
        
        // NOTE: Do not place your application dependencies here; they belong
        // in the individual module build.gradle files
    }
}
        
allprojects {
    repositories {
        google()
        mavenCentral()
        jcenter() // Warning: this repository is going to shut down soon
    }
}
        
task clean(type: Delete) {
    delete rootProject.buildDir
}
";
    }

    protected function appBuildGradle($isKotlin)
    {
        return '
plugins {
    id "com.android.application"
    ' . ($isKotlin ? 'id "kotlin-android"' : '') . '
}
        
android {
    compileSdkVersion ' . $this->getInputOption('compileSdk') . '
    buildToolsVersion "' . $this->getInputOption('buildTools') . '"
        
    defaultConfig {
        applicationId "' . $this->pkgName . '"
        minSdkVersion ' . $this->getInputOption('minSdk') . '
        targetSdkVersion ' . $this->getInputOption('targetSdk') . '
        versionCode 1
        versionName "1.0"
        
        testInstrumentationRunner "androidx.test.runner.AndroidJUnitRunner"
    }
        
    buildTypes {
        release {
            minifyEnabled false
            proguardFiles getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro"
        }
    }
    compileOptions {
        sourceCompatibility JavaVersion.VERSION_1_8
        targetCompatibility JavaVersion.VERSION_1_8
    }
    ' . ($isKotlin ? 'kotlinOptions {
        jvmTarget = "1.8"
    }' : '') . '
}
        
dependencies {
    
    ' . ($isKotlin ? 'implementation "org.jetbrains.kotlin:kotlin-stdlib:$kotlin_version"' : '') . '
    implementation "com.android.support:appcompat-v7:28.0.0"
    testImplementation "junit:junit:4.+"
    androidTestImplementation "com.android.support.test:runner:1.0.2"
    androidTestImplementation "com.android.support.test.espresso:espresso-core:3.0.2"
}
';
    }

    protected function gradleProperties($isKotlin)
    {
        return "
# Project-wide Gradle settings.
# IDE (e.g. Android Studio) users:
# Gradle settings configured through the IDE *will override*
# any settings specified in this file.
# For more details on how to configure your build environment visit
# http://www.gradle.org/docs/current/userguide/build_environment.html
# Specifies the JVM arguments used for the daemon process.
# The setting is particularly useful for tweaking memory settings.
org.gradle.jvmargs=-Xmx2048m -Dfile.encoding=UTF-8
# When configured, Gradle will run in incubating parallel mode.
# This option should only be used with decoupled projects. More details, visit
# http://www.gradle.org/docs/current/userguide/multi_project_builds.html#sec:decoupled_projects
# org.gradle.parallel=true
# Kotlin code style for this project: \"official\" or \"obsolete\":
" . ($isKotlin ? "kotlin.code.style=official" : '') . "
";
    }

    protected function manifestApplicationFile($moduleName)
    {
        return '
<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns:android="http://schemas.android.com/apk/res/android"
    package="' . $this->pkgName . '">
        
    <application
        android:allowBackup="true"
        android:label="@string/app_name"
        android:icon="@mipmap/ic_launcher"
        android:roundIcon="@mipmap/ic_launcher_round"
        android:supportsRtl="true"
        android:theme="@style/Theme.' .  ('app' === $moduleName ? $this->projectName : $moduleName) . '"/>
        
</manifest>
';
    }

    // TODO: 
    protected function manifestModuleFile($moduleName, $moduleType)
    {
        return '
<manifest xmlns:android="http://schemas.android.com/apk/res/android" 
    package="' . $this->pkgName . ('app' === $moduleName ? '' : '.' . $moduleName) . '">
    
    ' . ($moduleType === 'library' ? '' : '<application
        android:allowBackup="true"
        android:icon="@mipmap/ic_launcher"
        android:label="@string/app_name"
        android:roundIcon="@mipmap/ic_launcher_round"
        android:supportsRtl="true"
        android:theme="@style/AppTheme">
    </application>') . '
</manifest>
';
    }

    protected function proguardFile()
    {
        return <<<EOT
# Add project specific ProGuard rules here.
# You can control the set of applied configuration files using the
# proguardFiles setting in build.gradle.
#
# For more details, see
#   http://developer.android.com/guide/developing/tools/proguard.html

# If your project uses WebView with JS, uncomment the following
# and specify the fully qualified class name to the JavaScript interface
# class:
#-keepclassmembers class fqcn.of.javascript.interface.for.webview {
#   public *;
#}

# Uncomment this to preserve the line number information for
# debugging stack traces.
#-keepattributes SourceFile,LineNumberTable

# If you keep the line number information, uncomment this to
# hide the original source file name.
#-renamesourcefileattribute SourceFile
EOT;
    }

    protected function stringsFile($moduleName)
    {
        return '
<resources>
    <string name="app_name">' . ('app' === $moduleName ? $this->projectName : $moduleName) . '</string>
</resources>
';
    }

    protected function themesFile($moduleName, $isDark = false)
    {
        return '
<resources xmlns:tools="http://schemas.android.com/tools">
    <!-- Base application theme. -->
    <style name="Theme.' .  ('app' === $moduleName ? $this->projectName : $moduleName) . '" parent="Theme.MaterialComponents.DayNight.DarkActionBar">
        <!-- Primary brand color. -->
        <item name="colorPrimary">@color/purple_' . ($isDark ? 200 : 500) . '</item>
        <item name="colorPrimaryVariant">@color/purple_700</item>
        <item name="colorOnPrimary">@color/' . ($isDark ? 'black' : 'white') . '</item>
        <!-- Secondary brand color. -->
        <item name="colorSecondary">@color/teal_200</item>
        <item name="colorSecondaryVariant">@color/teal_' . ($isDark ? '200' : '700') . '</item>
        <item name="colorOnSecondary">@color/black</item>
        <!-- Status bar color. -->
        <item name="android:statusBarColor" tools:targetApi="l">?attr/colorPrimaryVariant</item>
        <!-- Customize your theme here. -->
    </style>
</resources>
';
    }
}
