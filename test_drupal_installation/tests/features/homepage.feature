@api
Feature: TestHomepage
  In order to check does site works
  As a visitor
  I need to go to homepage

  Scenario: Go to homepage
    Given I am on homepage
    Then I should see "Welcome to Drupal"
