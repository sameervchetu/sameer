@totara @totara_core
Feature: Test the ability to set your own
  position assignments on email-based self-enrolment

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname    | lastname | email               |
      | manager  | Frederick    | Newman   | manager@example.com |
    And the following "organisation frameworks" exist in "totara_hierarchy" plugin:
      | fullname               | idnumber  |
      | Organisation Framework | oframe    |
    And the following "organisations" exist in "totara_hierarchy" plugin:
      | fullname         | idnumber  | org_framework |
      | Organisation One | org1      | oframe        |
      | Organisation Two | org2      | oframe        |
    And the following "position frameworks" exist in "totara_hierarchy" plugin:
      | fullname           | idnumber  |
      | Position Framework | pframe    |
    And the following "positions" exist in "totara_hierarchy" plugin:
      | fullname     | idnumber  | pos_framework |
      | Position One | pos1      | pframe        |
      | Position Two | pos2      | pframe        |
    And I log in as "admin"
    And I navigate to "Email-based self-registration" node in "Site administration > Plugins > Authentication"
    And I click on "Yes" "option" in the "#menuallowsignupposition" "css_element"
    And I click on "Yes" "option" in the "#menuallowsignuporganisation" "css_element"
    And I click on "Yes" "option" in the "#menuallowsignupmanager" "css_element"
    And I press "Save changes"
    And I click on "Email-based self-registration" "option" in the "#id_s__registerauth" "css_element"
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Testing position assignment fields on email-based self-registration
    When I press "Create new account"
    Then I should see "Position"
    And I should see "Organisation"
    And I should see "Manager"

    When I set the following fields to these values:
      | Username      | gregnick             |
      | Password      | Greg_Nick01          |
      | Email address | gregnick@example.com |
      | Email (again) | gregnick@example.com |
      | First name    | Gregory              |
      | Surname       | Nickleson            |
    And I press "Choose position"
    And I click on "Position One" "link" in the "position" "totaradialogue"
    And I click on "OK" "button" in the "position" "totaradialogue"
    And I press "Choose organisation"
    And I click on "Organisation One" "link" in the "organisation" "totaradialogue"
    And I click on "OK" "button" in the "organisation" "totaradialogue"
    And I press "Choose manager"
    And I click on "Frederick Newman" "link" in the "manager" "totaradialogue"
    And I click on "OK" "button" in the "manager" "totaradialogue"
    And I press "Create my new account"
    And I press "Continue"
    And I log in as "admin"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "Confirm" "link" in the "Gregory Nickleson" "table_row"
    And I click on "Gregory Nickleson" "link"
    And I navigate to "Primary position" node in "Profile settings for Gregory Nickleson > Positions"
    Then I should see "Position One"
    And I should see "Organisation One"
    And I should see "Frederick Newman"
