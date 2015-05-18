@totara @totara_program
Feature: Users assignments to a program
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
      | user004  | fn_004    | ln_004   | user004@example.com |
      | user005  | fn_005    | ln_005   | user005@example.com |
    And the following "cohorts" exist:
      | name      | idnumber | contextlevel | reference |
      | Audience1 | aud1     | System       |           |
    And the following "cohort members" exist:
      | user    | cohort |
      | user002 | aud1   |
      | user003 | aud1   |
    And the following "organisation frameworks" exist in "totara_hierarchy" plugin:
      | fullname               | idnumber  |
      | Organisation Framework | oframe    |
    And the following "organisations" exist in "totara_hierarchy" plugin:
      | fullname         | idnumber  | org_framework |
      | Organisation One | org1      | oframe        |
      | Organisation Two | org2      | oframe        |
    And the following "organisation assignments" exist in "totara_hierarchy" plugin:
      | user    | organisation |
      | user001 | org1         |
      | user002 | org1         |
      | user003 | org2         |
      | user004 | org2         |
    And the following "position frameworks" exist in "totara_hierarchy" plugin:
      | fullname           | idnumber  |
      | Position Framework | pframe    |
    And the following "positions" exist in "totara_hierarchy" plugin:
      | fullname     | idnumber  | pos_framework |
      | Position One | pos1      | pframe        |
      | Position Two | pos2      | pframe        |
    And the following "position assignments" exist in "totara_hierarchy" plugin:
      | user    | position |
      | user001 | pos1     |
      | user002 | pos1     |
      | user003 | pos2     |
      | user004 | pos2     |
    And the following "manager assignments" exist in "totara_hierarchy" plugin:
      | user    | manager |
      | user001 | admin   |
      | user002 | user001 |
      | user003 | user001 |
      | user004 | user003 |
      | user005 | user004 |
    And the following "programs" exist in "totara_program" plugin:
      | fullname                 | shortname    |
      | Assignment Program Tests | assigntest   |

  @javascript
  Scenario: Test program assignments via individual assigments
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Assignment Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Assignments" "link"
    And I click on "Individuals" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add individuals to program" "button"
    And I click on "fn_001 ln_001 (user001@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "fn_002 ln_002 (user002@example.com)" "link" in the "add-assignment-dialog-5" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-5" "totaradialogue"
    And I press "Save changes"
    And I press "Save all changes"
    Then I should see "2 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

  @javascript
  Scenario: Test program assignments and updates via audience assigments
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Assignment Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Assignments" "link"
    And I click on "Audiences" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add audiences to program" "button"
    And I click on "Audience1" "link" in the "add-assignment-dialog-3" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-3" "totaradialogue"
    And I press "Save changes"
    And I press "Save all changes"
    Then I should see "2 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user004"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    When I log out
    And I log in as "admin"
    And the following "cohort members" exist:
      | user    | cohort |
      | user004 | aud1   |
    And I navigate to "Audiences" node in "Site administration > Users > Accounts"
    And I click on "Audience1" "link"
    And I click on "Edit members" "link"
    And I click on "fn_002 ln_002 (user002@example.com)" "option" in the "#removeselect" "css_element"
    And I click on "remove" "button"
    And I run the program assignments task

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user004"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

  @javascript
  Scenario: Test program assignments and updates via position assigments
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Assignment Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Assignments" "link"
    And I click on "Positions" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add position to program" "button"
    And I click on "Position One" "link" in the "add-assignment-dialog-2" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-2" "totaradialogue"
    And I press "Save changes"
    And I press "Save all changes"
    Then I should see "2 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    When the following "position assignments" exist in "totara_hierarchy" plugin:
      | user    | position |
      | user001 | pos2     |
      | user002 | pos1     |
      | user003 | pos1     |
    And I run the program assignments task

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

  @javascript
  Scenario: Test program assignments and updates via organisation assigments
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Assignment Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Assignments" "link"
    And I click on "Organisations" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add organisations to program" "button"
    And I click on "Organisation One" "link" in the "add-assignment-dialog-1" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-1" "totaradialogue"
    And I press "Save changes"
    And I press "Save all changes"
    Then I should see "2 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    And the following "organisation assignments" exist in "totara_hierarchy" plugin:
      | user    | organisation |
      | user001 | org2         |
      | user002 | org1         |
      | user003 | org1         |
    And I run the program assignments task

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

  @javascript
  Scenario: Test program assignments and updates via manager path assigments
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Assignment Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Assignments" "link"
    And I click on "Management hierarchy" "option" in the "#menucategory_select_dropdown" "css_element"
    And I click on "Add" "button" in the "#category_select" "css_element"
    And I click on "Add managers to program" "button"
    And I click on ".lastExpandable-hitarea" "css_element" in the "add-assignment-dialog-4" "totaradialogue"
    And I click on "fn_001 ln_001 (user001@example.com)" "link" in the "add-assignment-dialog-4" "totaradialogue"
    And I click on "Ok" "button" in the "add-assignment-dialog-4" "totaradialogue"
    And I press "Save changes"
    And I press "Save all changes"
    Then I should see "2 learner(s) assigned. 2 learner(s) are active, 0 with exception(s)"

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user004"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    When the following "manager assignments" exist in "totara_hierarchy" plugin:
      | user    | manager |
      | user001 | admin   |
      | user002 | admin   |
      | user003 | user001 |
      | user004 | user001 |
      | user005 | user002 |
    And I run the program assignments task

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I follow "Record of Learning"
    Then I should not see "Assignment Program Tests"

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"

    When I log out
    And I log in as "user004"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Assignment Program Tests"
