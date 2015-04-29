@totara @totara_program
Feature: Users can expand the program info
  In order to expand program info
  As a user
  I need to login if forcelogin enabled

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And I log in as "admin"
    And I focus on "Find Learning" "link"
    And I follow "Programs"
    And I press "Add a new program"
    And I press "Save changes"
    And I set the following administration settings values:
      | Enhanced catalog | 1 |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Allow not logged in users to expand program when forcelogin disabled
    Given I focus on "Find Learning" "link"
    And I follow "Programs"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "View program"

  @javascript
  Scenario: Allow guest account to expand program when forcelogin enabled
    Given I log in as "admin"
    And I set the following administration settings values:
      | forcelogin | 1 |
    And I log out
    And I click on "#guestlogin input[type=submit]" "css_element"
    And I focus on "Find Learning" "link"
    And I follow "Programs"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "View program"

  @javascript
  Scenario: Allow user to expand program when forcelogin enabled
    Given I log in as "admin"
    And I set the following administration settings values:
      | forcelogin | 1 |
    And I log out
    And I click on "#guestlogin input[type=submit]" "css_element"
    And I log in as "student1"
    And I focus on "Find Learning" "link"
    And I follow "Programs"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "View program"
