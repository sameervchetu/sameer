@totara @totara_certification
Feature: User recertification and expiry of certification
  In order to view a program
  As a user
  I need to login if forcelogin enabled

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email |
      | user001 | fn_001 | ln_001 | user001@example.com |
    And the following "courses" exist:
      | fullname         | shortname | format | enablecompletion | completionstartonenrol |
      | Certify Course   | CC1       | topics | 1                | 1                      |
      | Recertify Course | RC1       | topics | 1                | 1                      |
    And I log in as "admin"
    And I navigate to "Turn editing on" node in "Front page settings"
    And I set self completion for "Certify Course" in the "Miscellaneous" category
    And I set self completion for "Recertify Course" in the "Miscellaneous" category
    And I focus on "Find Learning" "link"
    And I follow "Certifications"
    And I press "Create Certification"
    And I set the following fields to these values:
        | Full name  | Test Certification |
        | Short name | tstcert            |
    And I press "Save changes"
    And I click on "Content" "link"
    And I click on "addcontent_ce" "button" in the "#programcontent_ce" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Certify Course" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I click on "addcontent_rc" "button" in the "#programcontent_rc" "css_element"
    And I click on "Miscellaneous" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Recertify Course" "link" in the "addmulticourse" "totaradialogue"
    And I click on "Ok" "button" in the "addmulticourse" "totaradialogue"
    And I press "Save changes"
    And I click on "Save all changes" "button"
    And I click on "Certification" "link" in the ".tabtree" "css_element"
    And I set the following fields to these values:
        | activenum | 6 |
        | windownum | 2 |
    And I click on "Month(s)" "option" in the "#id_activeperiod" "css_element"
    And I click on "Month(s)" "option" in the "#id_windowperiod" "css_element"
    And I click on "Use certification completion date" "option" in the "#id_recertifydatetype" "css_element"
    And I press "Save changes"
    And I click on "Save all changes" "button"
    And I log out
    And the following "program assignments" exist in "totara_program" plugin:
      | program | user    |
      | tstcert | user001 |

  # Test recertification path:
  # Initial Cert -> Recert -> Recert -> Expired -> Cert -> Recert
  @javascript
  Scenario: A user can recertify multiple times
    Given I log in as "user001"
    And I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Test Certification"
    And I should see "Certify Course"
    And I should not see "Recertify Course"

    When I click on "Certify Course" "link" in the ".display-program" "css_element"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Test Certification"
    And I should see "Completed"
    And I should see "Not due for renewal"

    When I wind back certification dates by 5 months
    And I run the recertification task
    And I click on "Courses" "link" in the "#dp-plan-content" "css_element"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Completed"
    And I should see "Due for renewal"

    When I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Test Certification"
    And I should see "Recertify Course"
    And I should not see "Certify Course"

    When I click on "Recertify Course" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Test Certification"
    And I should see "Completed"
    And I should see "Not due for renewal"

    When I wind back certification dates by 5 months
    And I run the recertification task
    And I click on "Courses" "link" in the "#dp-plan-content" "css_element"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Completed"
    And I should see "Due for renewal"

    When I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Test Certification"
    And I should see "Recertify Course"
    And I should not see "Certify Course"

    When I click on "Recertify Course" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Test Certification"
    And I should see "Complete"
    And I should see "Not due for renewal"

    When I wind back certification dates by 7 months
    And I run the recertification task
    And I click on "Courses" "link" in the "#dp-plan-content" "css_element"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Assigned"
    And I should see "Renewal expired"

    When I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Test Certification"
    And I should see "Overdue"
    And I should see "Test Certification"
    And I should see "Certify Course"
    And I should not see "Recertify Course"

    When I click on "Certify Course" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Test Certification"
    And I should see "Complete"
    And I should see "Not due for renewal"

    When I wind back certification dates by 5 months
    And I run the recertification task
    And I click on "Courses" "link" in the "#dp-plan-content" "css_element"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Complete"
    And I should see "Due for renewal"

    When I focus on "My Learning" "link"
    And I follow "Required Learning"
    Then I should see "Test Certification"
    And I should see "Recertify Course"
    And I should not see "Certify Course"

    When I click on "Recertify Course" "link"
    And I click on "Complete course" "link"
    And I click on "Yes" "button"
    And I focus on "My Learning" "link"
    Then I should not see "Required Learning"

    When I focus on "My Learning" "link"
    And I follow "Record of Learning"
    And I click on "Certifications" "link" in the "#dp-plan-content" "css_element"
    Then I should see "Test Certification"
    And I should see "Complete"
    And I should see "Not due for renewal"
