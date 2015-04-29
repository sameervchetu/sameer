@mod @totara @mod_facetoface
Feature: Add a face to face
  In order to run a seminar
  As a teacher
  I need to create a face to face activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | Terry1    | Teacher1 | teacher1@moodle.com |
      | student1 | Sam1      | Student1 | student1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "admin"
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Activity modules" node
    And I expand "Face-to-face" node
    And I follow "General Settings"
    And I set the following fields to these values:
      | Select position on signup | 1 |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Add and configure a facetoface activity with a single session and position asked for but not mandated then sign up as user with no pos
    When I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
      | Select position on signup | 1             |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown | Yes |
      | timestart[0][day] | 1 |
      | timestart[0][month] | 1 |
      | timestart[0][year] | 2020 |
      | timestart[0][hour] | 11 |
      | timestart[0][minute] | 00 |
      | timefinish[0][day] | 1 |
      | timefinish[0][month] | 1 |
      | timefinish[0][year] | 2020 |
      | timefinish[0][hour] | 12 |
      | timefinish[0][minute] | 00 |
    And I press "Save changes"
    And I should see "1 January 2020"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Sign-up"
    And I press "Sign-up"
    Then I should see "Your booking has been completed."

  @javascript
  Scenario: Add and configure a facetoface activity with a single session and position asked for but not mandated then
            sign in as user with two positions and check attendee list reflects this and the selected position can be updated
    Given the following "position" frameworks exist:
      | fullname      | idnumber |
      | PosHierarchy1 | FW001    |
    And the following "position" hierarchy exists:
      | framework | idnumber | fullname   |
      | FW001     | POS001   | Position1  |
      | FW001     | POS002   | Position2  |
    And the following position assignments exist:
      | user     | position | type      |
      | student1 | POS001   | primary   |
      | student1 | POS002   | secondary |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
      | Select position on signup | 1             |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown | Yes |
      | timestart[0][day] | 1 |
      | timestart[0][month] | 1 |
      | timestart[0][year] | 2020 |
      | timestart[0][hour] | 11 |
      | timestart[0][minute] | 00 |
      | timefinish[0][day] | 1 |
      | timefinish[0][month] | 1 |
      | timefinish[0][year] | 2020 |
      | timefinish[0][hour] | 12 |
      | timefinish[0][minute] | 00 |
    And I press "Save changes"
    And I should see "1 January 2020"
    And I log out
    When I log in as "student1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Sign-up"
    And I set the following fields to these values:
      | Select a position | Position2 |
    And I press "Sign-up"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Attendees"
    And I should see "Position2"
    And I log out
    And I log in as "admin"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Attendees"
    And I should see "Position2"
    And I click on ".attendee-edit-position" "css_element"
    And I set the following fields to these values:
      | selectposition | Position1 |
    And I press "Update position"
    And I should see "Position1"

  @javascript
  Scenario: Add and configure a facetoface activity with a single session and position asked for and mandated then try to sign up as user with no pos
    When I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
      | Select position on signup | 1             |
      | Prevent signup if no position is selected or can be found | 1             |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown | Yes |
      | timestart[0][day] | 1 |
      | timestart[0][month] | 1 |
      | timestart[0][year] | 2020 |
      | timestart[0][hour] | 11 |
      | timestart[0][minute] | 00 |
      | timefinish[0][day] | 1 |
      | timefinish[0][month] | 1 |
      | timefinish[0][year] | 2020 |
      | timefinish[0][hour] | 12 |
      | timefinish[0][minute] | 00 |
    And I press "Save changes"
    And I should see "1 January 2020"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Sign-up"
    Then I should see "You must have a suitable position assigned to sign up for this facetoface activity"

  @javascript
  Scenario: Add and configure a facetoface activity with a single session and position asked for then sign in as user with two positions and check user shown to correct manager.
    Given the following "position" frameworks exist:
      | fullname      | idnumber |
      | PosHierarchy1 | FW001    |
    And the following "position" hierarchy exists:
      | framework | idnumber | fullname   |
      | FW001     | POS001   | Position1  |
      | FW001     | POS002   | Position2  |
    And the following position assignments exist:
      | user     | position | type      |
      | student1 | POS001   | primary   |
      | student1 | POS002   | secondary |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name        | Test facetoface name        |
      | Description | Test facetoface description |
      | Select position on signup | 1             |
    And I follow "View all sessions"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | datetimeknown | Yes |
      | timestart[0][day] | 1 |
      | timestart[0][month] | 1 |
      | timestart[0][year] | 2020 |
      | timestart[0][hour] | 11 |
      | timestart[0][minute] | 00 |
      | timefinish[0][day] | 1 |
      | timefinish[0][month] | 1 |
      | timefinish[0][year] | 2020 |
      | timefinish[0][hour] | 12 |
      | timefinish[0][minute] | 00 |
    And I press "Save changes"
    And I should see "1 January 2020"
    And I log out
    When I log in as "student1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Sign-up"
    And I set the following fields to these values:
      | Select a position | Position2 |
    And I press "Sign-up"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "View all sessions"
    And I follow "Attendees"
    Then I should see "Position2"
