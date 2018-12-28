# Isolated Drupal Extension

This behat extension is intended to work together with [Drupal Extension] (https://github.com/jhedstrom/drupalextension) 
and uses Drupal multisite possibilities in order to run tests on separate test site installation.

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

Extension provides 2 configurable parameters: 
 - profile : the name of profile to install
 - reuse_installation: do not reinstall test site installation each time
