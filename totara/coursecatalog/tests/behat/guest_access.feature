@totara @totara_coursecatalog @enrol @enrol_guest
Feature: Guest users can auto-enrol themself via course catalog in courses where guest access is allowed
  In order to access courses contents
  As a guest
  I need to access courses as a guest

  Background:
    Given I am on a totara site
    And I log in as "admin"
    And I set the following administration settings values:
      | Enhanced catalog | 1 |
    And I press "Save changes"
    And I log out
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
      | student1 | Student | 1 | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Forum" to section "1" and I fill the form with:
      | Forum name | Test forum name |
      | Description | Test forum description |
    And I click on "Edit settings" "link" in the "Administration" "block"

  @javascript
  Scenario: Allow guest access through the course catalog without password
    Given I set the following fields to these values:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Find Learning"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "Guest access"
    And I press "Enrol"
    And I wait until the page is ready
    And I should see "Test forum name"

  @javascript
  Scenario: Allow guest access through the course catalog with password
    Given I set the following fields to these values:
      | Allow guest access | Yes |
      | Password | moodle_rules |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Find Learning"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "Guest access"
    And I set the following fields to these values:
      | Password | moodle_sucks |
    And I press "Enrol"
    And I should see "Incorrect access password, please try again"
    And I set the following fields to these values:
      | Password | moodle_rules |
    And I press "Enrol"
    And I wait until the page is ready
