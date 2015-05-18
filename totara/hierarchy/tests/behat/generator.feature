@totara @totara_hierarchy @totara_generator
Feature: Behat generators for hierarchies work
  In order to use behat generators
  As a behat writer
  I need to be able to create hierarchies via behat generator

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | user001  | fn_001    | ln_001   | user001@example.com |
      | user002  | fn_002    | ln_002   | user002@example.com |

  @javascript
  Scenario: Verify the program generators work
    Given the following "organisation frameworks" exist in "totara_hierarchy" plugin:
      | fullname               | idnumber |
      | Organisation Framework | oframe   |
    And the following "organisations" exist in "totara_hierarchy" plugin:
      | fullname         | idnumber | org_framework |
      | Organisation One | org1     | oframe        |
      | Organisation Two | org2     | oframe        |
    And the following "organisation assignments" exist in "totara_hierarchy" plugin:
      | user    | organisation |
      | user001 | org1         |
      | user002 | org2         |
    And the following "position frameworks" exist in "totara_hierarchy" plugin:
      | fullname           | idnumber |
      | Position Framework | pframe   |
    And the following "positions" exist in "totara_hierarchy" plugin:
      | fullname     | idnumber | pos_framework |
      | Position One | pos1     | pframe        |
      | Position Two | pos2     | pframe        |
    And the following "position assignments" exist in "totara_hierarchy" plugin:
      | user    | position |
      | user001 | pos1     |
      | user002 | pos2     |
    And the following "manager assignments" exist in "totara_hierarchy" plugin:
      | user    | manager |
      | user001 | admin   |
      | user002 | user001 |

    When I log in as "admin"
    And I navigate to "Manage positions" node in "Site administration > Hierarchies > Positions"
    Then I should see "Position Framework"

    When I click on "Position Framework" "link" in the "#frameworkstable" "css_element"
    Then I should see "Position One"
    And I should see "Position Two"

    When I navigate to "Manage organisations" node in "Site administration > Hierarchies > Organisations"
    Then I should see "Organisation Framework"

    When I click on "Organisation Framework" "link" in the "#frameworkstable" "css_element"
    Then I should see "Organisation One"
    And I should see "Organisation Two"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "fn_001 ln_001" "link"
    Then I should see "Position One" in the ".descriptionbox" "css_element"
    And I should see "Organisation One" in the ".descriptionbox" "css_element"
    And I should see "Admin User" in the ".descriptionbox" "css_element"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "fn_002 ln_002" "link"
    Then I should see "Position Two" in the ".descriptionbox" "css_element"
    And I should see "Organisation Two" in the ".descriptionbox" "css_element"
    And I should see "fn_001 ln_001" in the ".descriptionbox" "css_element"

  @javascript
  Scenario: Verify the user interface works the same as program generators
    Given I log in as "admin"

    When I navigate to "Manage positions" node in "Site administration > Hierarchies > Positions"
    And I click on "Add new position framework" "button"
    And I set the following fields to these values:
        | fullname | Position Framework |
        | idnumber | pframe             |
    And I press "Save changes"
    Then I should see "Position Framework"

    When I click on "Position Framework" "link" in the "#frameworkstable" "css_element"
    And I click on "Add new position" "button"
    And I set the following fields to these values:
        | fullname | Position One |
        | idnumber | pos1         |
    And I press "Save changes"
    And I press "Return to position framework"
    And I click on "Add new position" "button"
    And I set the following fields to these values:
        | fullname | Position Two |
        | idnumber | pos2         |
    And I press "Save changes"
    And I press "Return to position framework"
    Then I should see "Position One"
    And I should see "Position Two"

    When I navigate to "Manage organisations" node in "Site administration > Hierarchies > Organisations"
    And I click on "Add new organisation framework" "button"
    And I set the following fields to these values:
        | fullname | Organisation Framework |
        | idnumber | oframe                 |
    And I press "Save changes"
    Then I should see "Organisation Framework"

    When I click on "Organisation Framework" "link" in the "#frameworkstable" "css_element"
    And I click on "Add new organisation" "button"
    And I set the following fields to these values:
        | fullname | Organisation One |
        | idnumber | org1             |
    And I press "Save changes"
    And I press "Return to organisation framework"
    And I click on "Add new organisation" "button"
    And I set the following fields to these values:
        | fullname | Organisation Two |
        | idnumber | org2             |
    And I press "Save changes"
    And I press "Return to organisation framework"
    Then I should see "Organisation One"
    And I should see "Organisation Two"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "fn_001 ln_001" "link"
    And I navigate to "Primary position" node in "Profile settings for fn_001 ln_001 > Positions"
    And I click on "Choose position" "button"
    And I click on "Position One" "link" in the "position" "totaradialogue"
    And I click on "OK" "button" in the "position" "totaradialogue"
    And I click on "Choose organisation" "button"
    And I click on "Organisation One" "link" in the "organisation" "totaradialogue"
    And I click on "OK" "button" in the "organisation" "totaradialogue"
    And I click on "Choose manager" "button"
    And I click on "Admin User" "link" in the "manager" "totaradialogue"
    And I click on "OK" "button" in the "manager" "totaradialogue"
    And I press "Update position"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "fn_001 ln_001" "link"
    Then I should see "Position One" in the ".descriptionbox" "css_element"
    And I should see "Organisation One" in the ".descriptionbox" "css_element"
    And I should see "Admin User" in the ".descriptionbox" "css_element"

    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "fn_002 ln_002" "link"
    And I navigate to "Primary position" node in "Profile settings for fn_002 ln_002 > Positions"
    And I click on "Choose position" "button"
    And I click on "Position Two" "link" in the "position" "totaradialogue"
    And I click on "OK" "button" in the "position" "totaradialogue"
    And I click on "Choose organisation" "button"
    And I click on "Organisation Two" "link" in the "organisation" "totaradialogue"
    And I click on "OK" "button" in the "organisation" "totaradialogue"
    And I click on "Choose manager" "button"
    And I click on "fn_001 ln_001" "link" in the "manager" "totaradialogue"
    And I click on "OK" "button" in the "manager" "totaradialogue"
    And I press "Update position"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "fn_002 ln_002" "link"
    Then I should see "Position Two" in the ".descriptionbox" "css_element"
    And I should see "Organisation Two" in the ".descriptionbox" "css_element"
    And I should see "fn_001 ln_001" in the ".descriptionbox" "css_element"
