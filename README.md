# Isolated Drupal Extension

This behat extension is intended to work together with [Drupal Extension](https://github.com/jhedstrom/drupalextension) 
and uses Drupal multisite possibilities in order to run tests on separate test site installation.

It works much more like Drupal's `BrowserTest`.
If you have a Drupal site and run a `behat` command then :
 - A new site will be created at `sites/simpletest/{timestamp}` with an SQLite database and with given in configuration profile name.
 - Behat tests will run on this new installation.
 - At the end of testing, `sites/simpletest` directory will be cleaned up if `reuse_installation` configuration value is set to TRUE.

### Requirements
This extension is tested with `Drupal 8.6`.
Previous versions are not supported.

### Quick start

Modify your `behat.yml`, add next lines : 

  ``` yaml
    extensions:
      Drupal\DrupalExtension:
       ...
       ...
      undertext\idrupalextension\IsolatedDrupalExtension:
        profile: "standard"
        reuse_installation: TRUE

  ```

This extension provides 2 configurable parameters: 
 - profile : the name of the profile to install
 - reuse_installation: do not reinstall test site installation each time

[![Build Status](https://travis-ci.com/undertext/idrupalextension.svg?branch=master)](https://travis-ci.com/undertext/idrupalextension)