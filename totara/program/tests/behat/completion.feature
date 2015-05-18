@totara @totara_program
Feature: Users completion of programs and coursesets
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
      | Course 2 | C2        | topics | 1                | 1                      |
      | Course 3 | C3        | topics | 1                | 1                      |
    And the following "programs" exist in "totara_program" plugin:
      | fullname                 | shortname  |
      | Completion Program Tests | comptest   |
    And the following "program assignments" exist in "totara_program" plugin:
      | program  | user    |
      | comptest | user001 |
      | comptest | user002 |
      | comptest | user003 |
    And I log in as "admin"
    And I navigate to "Turn editing on" node in "Front page settings"
    And I set self completion for "Course 1" in the "Miscellaneous" category
    And I set self completion for "Course 2" in the "Miscellaneous" category
    And I set self completion for "Course 3" in the "Miscellaneous" category
    And I log out

  # Completion of a program with content like so:
  # Course set 1 [ Course 1 And Course 2]
  # Then
  # Course set 2 [ Course 3]
  @javascript
  Scenario: Test program completion with courseset "AND"
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Completion Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Content" "link"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I press "Save changes"
    And I click on "Save all changes" "button"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Completion Program Tests"
    And I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"

    When I click on "Course 1" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "0%" program progress
    And I should see "Complete" in the ".r0 .coursecompletionstatus .completion-complete" "css_element"

    When I click on "Course 2" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "50%" program progress
    And I should see "Complete" in the ".r0 .coursecompletionstatus .completion-complete" "css_element"
    And I should see "Complete" in the ".r1 .coursecompletionstatus .completion-complete" "css_element"

    When I click on "Course 3" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Programs" "link" in the "#dp-plan-content" "css_element"
    And I click on "Completion Program Tests" "link"
    Then I should see "100%" program progress

  # Completion of a program with content like so:
  # Course set 1 [ Course 1 Or Course 2]
  # Or
  # Course set 2 [ Course 3]
  @javascript
  Scenario: Test program completion with courseset "OR"
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Completion Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Content" "link"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I click on "One course" "option" in the ".completiontype" "css_element"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I click on "or" "option" in the ".nextsetoperator-then" "css_element"
    And I press "Save changes"
    And I click on "Save all changes" "button"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Completion Program Tests"
    And I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"

    When I click on "Course 1" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Programs" "link" in the "#dp-plan-content" "css_element"
    And I click on "Completion Program Tests" "link"
    Then I should see "100%" program progress

    When I log out
    And I log in as "user002"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Completion Program Tests"

    When I click on "Course 2" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Programs" "link" in the "#dp-plan-content" "css_element"
    And I click on "Completion Program Tests" "link"
    Then I should see "100%" program progress

    When I log out
    And I log in as "user003"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Completion Program Tests"

    When I click on "Course 3" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Programs" "link" in the "#dp-plan-content" "css_element"
    And I click on "Completion Program Tests" "link"
    Then I should see "100%" program progress

  # Completion of a program with content like so:
  # Course set 1 [ Any 2 of Course 1, Course 2, Course 3]
  @javascript
  Scenario: Test program completion with courseset "XofY"
    Given I log in as "admin"
    And I navigate to "Manage programs" node in "Site administration > Courses"
    And I click on "Miscellaneous" "link"
    And I click on "Completion Program Tests" "link"
    And I click on "Edit program details" "button"
    And I click on "Content" "link"
    And I click on "addcontent_ce" "button" in the "#edit-program-content" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 1" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 2" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Course 3" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I click on "Some courses" "option" in the ".completiontype" "css_element"
    And I set "Minimum courses completed" for courseset "Untitled set" to "2"
    And I press "Save changes"
    And I click on "Save all changes" "button"

    When I log out
    And I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Completion Program Tests"
    And I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"

    When I click on "Course 1" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Completion Program Tests"
    And I should see "0%" program progress

    When I click on "Course 2" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Programs" "link" in the "#dp-plan-content" "css_element"
    And I click on "Completion Program Tests" "link"
    Then I should see "100%" program progress
