@local @local_consentmanager @javascript
Feature: Consent banner appears, accepts and remembers choice
  In order to comply with GDPR
  As a Moodle visitor
  I need to be asked for consent before third-party services run

  Background:
    Given the following config values are set as admin:
      | enabled  | 1 | local_consentmanager |
      | revision | 1 | local_consentmanager |

  Scenario: Anonymous visitor sees the banner on the login page
    When I am on site homepage
    Then "#local-consentmanager-banner" "css_element" should exist
    And I should see "Accept all" in the "#local-consentmanager-banner" "css_element"

  Scenario: Accept-all hides the banner and persists across reloads
    Given I am on site homepage
    And I click on "[data-action=\"acceptall\"]" "css_element"
    When I reload the page
    Then "#local-consentmanager-banner" "css_element" should not be visible

  Scenario: Essential-only accept hides the banner
    Given I am on site homepage
    When I click on "[data-action=\"acceptessential\"]" "css_element"
    And I reload the page
    Then "#local-consentmanager-banner" "css_element" should not be visible

  Scenario: Settings view shows all categories with essential locked
    Given I am on site homepage
    When I click on "[data-action=\"showdetails\"]" "css_element"
    Then ".consentmanager-details" "css_element" should be visible
    And ".consentmanager-toggle[disabled]" "css_element" should exist

  Scenario: Banner returns after admin bumps the revision
    Given I am on site homepage
    And I click on "[data-action=\"acceptall\"]" "css_element"
    And I reload the page
    And "#local-consentmanager-banner" "css_element" should not be visible
    When the following config values are set as admin:
      | revision | 2 | local_consentmanager |
    And I reload the page
    Then "#local-consentmanager-banner" "css_element" should be visible

  Scenario: My-consents link only shown to logged-in users
    Given the following "users" exist:
      | username | firstname | lastname | email          |
      | student1 | Student   | One      | s1@example.com |
    When I am on site homepage
    Then ".consentmanager-meta" "css_element" should not exist
    And I log in as "student1"
    And I am on site homepage
    And ".consentmanager-meta" "css_element" should exist
