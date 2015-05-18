@totara @totara_program
Feature: Generation of program assignment exceptions
  In order to view a program
  As a user
  I need to login if forcelogin enabled

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | user001  | fn_001    | ln_001   | user001@example.com |
      | user002  | fn_002    | ln_002   | user002@example.com |
      | user003  | fn_003    | ln_003   | user003@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | enablecompletion | completionstartonenrol |
      | Course 1 | C1        | topics | 1                | 1                      |
    And the following "programs" exist in "totara_program" plugin:
      | fullname                 | shortname |
      | Program Exception Tests  | exctest   |

  @javascript
  Scenario: Time allowance exceptions are generated and set to a realistic time
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Program Exception Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Content" "link"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I wait "2" seconds
    And I set "Minimum time required" for courseset "Untitled set" to "14"
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"

    When I click on "Assignments" "link"
    And I click on "Individuals" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add individuals to program" "button"
    And I click on "fn_001 ln_001 (user001@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "fn_002 ln_002 (user002@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait "2" seconds
    And I click on "Set completion" "link" in the ".completionlink_3" "css_element"
    And I click on "Week(s)" "option" in the "#timeperiod" "css_element"
    And I click on "Program enrollment date" "option" in the "#eventtype" "css_element"
    And I set the following fields to these values:
        | timeamount | 1 |
    And I click on "Set time relative to event" "button" in the "completion-dialog" "totaradialogue"
    And I click on "Set completion" "link" in the ".completionlink_4" "css_element"
    And I click on "Week(s)" "option" in the "#timeperiod" "css_element"
    And I click on "Program enrollment date" "option" in the "#eventtype" "css_element"
    And I set the following fields to these values:
        | timeamount | 2 |
    And I click on "Set time relative to event" "button"
    And I wait "2" seconds
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    Then I should see "2 learner(s) assigned. 1 learner(s) are active, 1 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Program Exception Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Program Exception Tests" in the "#program-content" "css_element"
    And I should see "Course 1" in the "#program-content" "css_element"

    When I log out
    And I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Program Exception Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Exception Report (1)" "link"
    Then I should see "fn_001 ln_001"
    And I should see "Time allowance"

    When I click on "All \"time allowance\" issues" "option" in the "#selectiontype" "css_element"
    And I click on "Set realistic time allowance" "option" in the "#selectionaction" "css_element"
    And I click on "Proceed with this action" "button"
    And I click on "OK" "button"
    Then I should see "No exceptions"
    And I should see "2 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I click on "Assignments" "link"
    And I click on "Add individuals to program" "button"
    And I click on "fn_003 ln_003 (user003@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait "2" seconds
    And I click on "Set completion" "link" in the ".completionlink_5" "css_element"
    And I click on "Week(s)" "option" in the "#timeperiod" "css_element"
    And I click on "Program enrollment date" "option" in the "#eventtype.eventtype" "css_element"
    And I set the following fields to these values:
        | timeamount | 3 |
    And I click on "Set time relative to event" "button"
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    Then I should see "3 learner(s) assigned. 3 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Program Exception Tests" in the "#program-content" "css_element"
    And I should see "Course 1" in the "#program-content" "css_element"

    When I click on "Course 1" "link" in the "#program-content" "css_element"
    Then I should see "You have been enrolled in course Course 1 via required learning program Program Exception Tests"

  @javascript
  Scenario: Already assigned exceptions are generated and overridden
    Given I log in as "admin"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "fn_001 ln_001" "link"
    And I click on "Learning Plans" "link" in the "#region-main" "css_element"
    And I press "Create new learning plan"
    And I press "Create plan"
    And I click on "Programs" "link" in the "#dp-plan-content" "css_element"
    And I press "Add programs"
    And I click on "Miscellaneous" "link" in the "assignprograms" "totaradialogue"
    And I click on "Program Exception Tests" "link" in the "assignprograms" "totaradialogue"
    And I click on "Save" "button" in the "assignprograms" "totaradialogue"
    And I wait "1" seconds
    And I click on "Manage plans" "link" in the "#dp-plans-menu" "css_element"
    And I click on "Approve" "link" in the "#dp-plans-list-unapproved-plans" "css_element"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Program Exception Tests" "link"
    And I click on "Edit program details" "button"

    When I click on "Assignments" "link"
    And I click on "Individuals" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add individuals to program" "button"
    And I click on "fn_001 ln_001 (user001@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "fn_002 ln_002 (user002@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait "2" seconds
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    Then I should see "2 learner(s) assigned. 1 learner(s) are active, 1 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Program Exception Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Program Exception Tests" in the "#program-content" "css_element"

    When I log out
    And I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Program Exception Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Exception Report (1)" "link"
    Then I should see "fn_001 ln_001"
    And I should see "User already assigned to program via learning plan"

    When I click on "All \"already assigned\" issues" "option" in the "#selectiontype" "css_element"
    And I click on "Override and add program" "option" in the "#selectionaction" "css_element"
    And I click on "Proceed with this action" "button"
    And I click on "OK" "button"
    Then I should see "No exceptions"
    And I should see "2 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I click on "Assignments" "link"
    And I click on "Add individuals to program" "button"
    And I click on "fn_003 ln_003 (user003@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait "2" seconds
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    Then I should see "3 learner(s) assigned. 3 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Program Exception Tests" in the "#program-content" "css_element"

  @javascript
  Scenario: Completion time unknown Exceptions are generated and dismissed
    Given I log in as "admin"
    And I navigate to "User profile fields" node in "Site administration > Users > Accounts"
    And I click on "Date/Time" "option" in the ".singleselect" "css_element"
    And I set the following fields to these values:
        | Short name | datetime    |
        | Name       | Date & Time |
    And I click on "param3" "checkbox"
    And I click on "Save changes" "button"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Program Exception Tests" "link"
    And I click on "Edit program details" "button"

    When I click on "Assignments" "link"
    And I click on "Individuals" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add individuals to program" "button"
    And I click on "fn_001 ln_001 (user001@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "fn_002 ln_002 (user002@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait "2" seconds
    And I click on "Set completion" "link" in the ".completionlink_3" "css_element"
    And I click on "Week(s)" "option" in the "#timeperiod" "css_element"
    And I click on "Profile field date" "option" in the "#eventtype" "css_element"
    And I click on "Date & Time" "link" in the "completion-event-dialog" "totaradialogue"
    And I click on "Ok" "button" in the "completion-event-dialog" "totaradialogue"
    And I wait "2" seconds
    And I set the following fields to these values:
        | timeamount | 2 |
    And I click on "Set time relative to event" "button" in the "completion-dialog" "totaradialogue"
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    Then I should see "2 learner(s) assigned. 1 learner(s) are active, 1 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Program Exception Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Program Exception Tests" in the "#program-content" "css_element"

    When I log out
    And I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Program Exception Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Exception Report (1)" "link"
    Then I should see "fn_001 ln_001"
    And I should see "Completion time unknown"

    When I click on "All \"completion time unknown\" issues" "option" in the "#selectiontype" "css_element"
    And I click on "Dismiss and take no action" "option" in the "#selectionaction" "css_element"
    And I click on "Proceed with this action" "button"
    And I click on "OK" "button"
    Then I should see "No exceptions"
    And I should see "2 learner(s) assigned. 1 learner(s) are active, 0 with exception(s)"

    When I click on "Assignments" "link"
    And I click on "Add individuals to program" "button"
    And I click on "fn_003 ln_003 (user003@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I wait "2" seconds
    And I click on "Save changes" "button"
    And I click on "Save all changes" "button"
    Then I should see "3 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Program Exception Tests"
