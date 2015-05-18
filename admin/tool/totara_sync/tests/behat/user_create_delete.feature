@totara @tool @tool_totara_sync @_file_upload
Feature: An admin can import users through HR import
  In order to test HR import of users
  I must log in as an admin and import from a CSV file

  Background:
    Given I log in as "admin"
    And I navigate to "General settings" node in "Site administration > HR Import"
    And I set the following fields to these values:
        | File Access | Upload Files |
    And I press "Save changes"
    And I navigate to "Manage elements" node in "Site administration > HR Import > Elements"
    And I "Enable" the "User" HR Import element
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Source | CSV |
    And I press "Save changes"
    And I navigate to "CSV" node in "Site administration > HR Import > Sources > User"
    And I set the following fields to these values:
      | First name | 1 |
      | Last name | 1 |
      | Email | 1 |
      | City | 1 |
      | Country | 1 |
    And I press "Save changes"
    And I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users.01.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    And I should see "Running HR Import cron...Done!"
    And I navigate to "HR Import Log" node in "Site administration > HR Import"
    And I should not see "Error" in the "#totarasynclog" "css_element"

  @javascript
  Scenario: import users through HR import
    Given I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    Then I should see "Import User001"
    And I should see "Import User002"
    And I should see "Import User003"

  @javascript
  Scenario: reimport users through HR import
    Given I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users.01.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    And I should see "Running HR Import cron...Done!"
    And I navigate to "HR Import Log" node in "Site administration > HR Import"
    And I should not see "Error" in the "#totarasynclog" "css_element"
    When I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    Then I should see "Import User001"
    And I should see "Import User002"
    And I should see "Import User003"

  @javascript
  Scenario: import a deleted user through HR import so they are undeleted
    Given I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I set the following fields to these values:
      | realname | 003 |
    And I press "Add filter"
    And I should not see "Import User002"
    And I should not see "Import User002"
    And I should see "Import User003"
    And I click on "Delete" "link"
    And I should see "Are you absolutely sure you want to completely delete 'Import User003'"
    And I press "Delete"
    And I press "Remove all filters"
    And I click on "Show more..." "link"
    And I set the following fields to these values:
      | Deleted | No |
    And I press "Add filter"
    And I should see "Import User001"
    And I should see "Import User002"
    And I should not see "Import User003"
    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users.01.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    And I should see "Running HR Import cron...Done!"
    Then I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "Show more..." "link"
    And I set the following fields to these values:
      | Deleted | No |
    And I press "Add filter"
    And I should see "Import User001"
    And I should see "Import User002"
    And I should see "Import User003"

  @javascript
  Scenario: import a deleted user through HR import with configuration to prevent undelete
    Given I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I set the following fields to these values:
      | realname | 003 |
    And I press "Add filter"
    And I should not see "Import User002"
    And I should not see "Import User002"
    And I should see "Import User003"
    And I click on "Delete" "link"
    And I should see "Are you absolutely sure you want to completely delete 'Import User003'"
    And I press "Delete"
    And I press "Remove all filters"
    And I click on "Show more..." "link"
    And I set the following fields to these values:
      | Deleted | No |
    And I press "Add filter"
    And I should see "Import User001"
    And I should see "Import User002"
    And I should not see "Import User003"
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | allow_create | 0 |
    And I press "Save changes"
    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users.01.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    And I should see "Running HR Import cron...Done! However, there have been some problems"
    Then I navigate to "HR Import Log" node in "Site administration > HR Import"
    And I should see "cannot undelete user imp003"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "Show more..." "link"
    And I set the following fields to these values:
      | Deleted | No |
    And I press "Add filter"
    And I should see "Import User001"
    And I should see "Import User002"
    And I should not see "Import User003"

  @javascript
  Scenario: import a deleted user through HR import with configuration to prevent undelete and complete sources
    Given I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I set the following fields to these values:
      | realname | 003 |
    And I press "Add filter"
    And I should not see "Import User002"
    And I should not see "Import User002"
    And I should see "Import User003"
    And I click on "Delete" "link"
    And I should see "Are you absolutely sure you want to completely delete 'Import User003'"
    And I press "Delete"
    And I press "Remove all filters"
    And I click on "Show more..." "link"
    And I set the following fields to these values:
      | Deleted | No |
    And I press "Add filter"
    And I should see "Import User001"
    And I should see "Import User002"
    And I should not see "Import User003"
    And I navigate to "User" node in "Site administration > HR Import > Elements"
    And I set the following fields to these values:
      | Source contains all records | Yes |
      | allow_create | 0 |
    And I press "Save changes"
    When I navigate to "Upload HR Import files" node in "Site administration > HR Import > Sources"
    And I upload "admin/tool/totara_sync/tests/fixtures/users.01.csv" file to "CSV" filemanager
    And I press "Upload"
    And I should see "HR Import files uploaded successfully"
    And I navigate to "Run HR Import" node in "Site administration > HR Import"
    And I press "Run HR Import"
    And I should see "Running HR Import cron...Done! However, there have been some problems"
    Then I navigate to "HR Import Log" node in "Site administration > HR Import"
    And I should see "cannot undelete user imp003"
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "Show more..." "link"
    And I set the following fields to these values:
      | Deleted | No |
    And I press "Add filter"
    And I should see "Import User001"
    And I should see "Import User002"
    And I should not see "Import User003"