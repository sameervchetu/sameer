<?php
/*

Release 2.7.0 (2nd March 2015):
==================================================

New features:
    T-10087  Added new editing and configuration interface for the main menu
    T-11033  Added new badge report source
    T-11185  Added new dynamic Appraisals advanced feature
    T-12199  Added new audience based visibility setting for courses, programs and certifications
    T-12325  Added new course completion date filter for the Record Of Learning all courses report
    T-12772  Added new aggregation and grouping column options in Reportbuilder reports
    T-12892  Added new cut off date and minimum capacity options for Facetoface sessions
    T-12900  Added new self approval feature for Facetoface sessions
    T-12902  Added new feature to allow users to register their interest in a Facetoface session they cannot sign up for
    T-13092  Added new Facetoface course enrolment plugin added to Totara
    T-13093  Added direct enrolment into Facetoface sessions from the enhanced catalog interface
    T-13114  Added feature to allow users to select a position when signing up for a Facetoface session
    T-13195  Added new autowaitlist and signup lottery in Facetoface
    T-13393  Added new setting to log user out of all sessions when their password is changed
    T-13407  Added new page to view all of a user's active sessions
    T-13468  Added new site log report source
    T-13502  Added graphical reporting to Reportbuilder
    T-13510  Added new upgrade log report source
    T-13514  Added new feature to reset main menu to defaults
    T-13693  Added completion status columns to the course completion report source
    T-13713  Added 'is completed', 'is in progress', and 'is not yet started' columns to programs and certifications report sources
    T-13714  Added new percentage aggregation option in Reportbuilder
    T-13715  Added new options to control the audience visibility of main menu items
    T-13718  Added custom fields to Facetoface sessions, signups and cancellations
    T-13721  Added ability to use user placeholders in main menu URLs
    T-13732  Added new audience based dashboards
    T-13765  Added new aggregated question types to Appraisals
    T-14081  Added MySQL configuration checks to the environment page

General improvements:
    T-9537   Added the ability to include children when filtering course categories in Reportbuilder
    T-9800   Improved the including of JS required by Reportbuilder filters
    T-11043  Improved handling when appraisee is missing roles involved in an Appraisal
    T-12309  Added link to the secure page layout in the element library
    T-12344  Improved use of forms when adding links to the Quicklinks block
    T-12388  Improved message when no results are returned for a program search
    T-12450  Added ability to control whether Facetoface session cancellation is available to learners
    T-12586  Removed totara-expanded-width class applied via JS
    T-12592  Fixed database schemas between installed sites and upgraded sites
    T-12662  Renamed Totara Sync to HR Import
    T-12712  Improved number of content types that can be created by the Test Site Generator
    T-12927  Added a new option to hide records between two dates in Reportbuilder date filters
    T-12929  Allowed more user profile fields to be used in Facetoface notifications via substitution
    T-13104  Changed Facetoface session duration to be stored in seconds instead of minutes
    T-13221  Added visibility controls to Appraisal redisplay questions
    T-13303  Introduced rb_display_nice_datetime_in_timezone method to complete timezone function suite
    T-13335  Improved main menu JS dependencies
    T-13338  Removed Recaptcha login protection in lieu of the new user lockout support
    T-13363  Upgraded Totara cron tasks to scheduled tasks
    T-13370  Changed Fusion Reportbuilder export to be longer enabled by default
    T-13383  Prevented admin redirection to enrolment page after course creation
    T-13408  Improved flexibility of which courses need to be completed to complete a Program courseset
    T-13526  Added a debug option to embedded reports
    T-13527  Added new Reportbuilder nice_date column format for displaying date information
    T-13575  Converted set_time_limit calls to use core_php_time_limit::raise instead
    T-13604  Improved handling of Totara user events to match Moodle behaviour
    T-13605  Added support for Totara-specific post upgrade steps
    T-13608  Added display of Standard Totara footer on popup pages
    T-13615  Added performance improvements for bulk user enrolments and role assignments
    T-13643  Removed W3C deprecated HTML tags
    T-13690  Added ability to specify password rotation as part of the password policy
    T-13716  Added ability to prevent cancellations within a certain time of a Facetoface session date
    T-13762  Fixed Reportbuilder file exports to now use parent folder permissions.
    T-13789  Removed unused function prog_get_all_users_programs
    T-13838  Added ability to withdraw from pending enrolments within the enhanced catalogue
    T-13980  Increased maximum database table name length
    T-14053  Improved timezone handling across the entire codebase

Accessibility and usability improvements:
    T-11919  Cleaned up nested HTML tables in toolbars
    T-12013  Improved the display of links in the Kiwifruit Responsive theme
    T-12332  Fixed current item in the navbar being incorrectly shown as a link
    T-12333  Added headings to many pages where there was no heading
    T-12334  Improved contrast of help icons
    T-12340  Converted labels with no associated input to more appropriate elements
    T-12350  Added a heading to the delete Learning Plan page
    T-12354  Added a heading to the form used to create a Learning Plan
    T-12356  Improved alt text for the date selector
    T-12358  Improved title attribute on the tab name when editing a Learning Plan
    T-12359  Increased contrast of notification messages for usability
    T-12367  Fixed positioning of the Save and Cancel buttons in hierarchy dialogues
    T-12374  Fixed multiple h1 elements on the my reports page
    T-12380  Added a heading to the delete scheduled report page
    T-12389  Removed incorrect fieldset HTML elements on Program content tab
    T-12390  Added missing title to the launch course column when viewing an assigned Program
    T-12398  Changed display of ical image to text, and increased the contrast, when viewing the calendar
    T-12415  Converted calendar export to use mforms
    T-12418  Added a heading to the file upload dialogue
    T-12423  Fixed keyboard navigation on user positions page
    T-12424  Fixed the labels for start and end date on the user positions pages
    T-12685  Converted add new user link to a button on the browse users page
    T-13011  Removed HTML tables from Message alerts
    T-13016  Cleaned up nested HTML tables in the alerts report
    T-13702  Converted to mforms date selector when creating Learning Plans
    T-13076  Converted to mforms date selector when creating new Audiences
    T-13089  Converted to mforms date selector when creating and editing Programs
    T-13304  Improved display of instant filtering in Reportbuilder
    T-13360  Improved the display markup of the users current session in a Facetoface module
    T-13362  Removed nested HTML tables in Facetoface events
    T-13369  Improved Facetoface block session filter HTML
    T-13515  Improved HTML layout of calendar filters in Facetoface block
    T-13609  Improved HTML layout when viewing messages
    T-13614  Improved HTML layout when viewing course groups
    T-13705  Fixed positioning of cancel and save buttons
    T-13728  Improved heading hierarchy on required learning pages
    T-13741  Improved HTML layout of web services documentation
    T-13791  Removed HTML table when viewing another user's Required Learning
    T-13804  Added a label to all duration based admin settings
    T-13949  Improved the alt text on images in the statistics block
    T-13999  Added missing labels to time selectors in admin settings

Feature details
===============

== Main menu customisations (T-10087, T-13514, T-13715, T-13721) ==
The following improvements have been made to the main menu:
1. It is now possible to customise the main menu by adding and removing your own items.
   Existing items cannot at present be deleted, as they may be relied upon for navigation.
2. The placement (sorting) of all items in the menu can be customised to your liking.
3. The visibility of all menu items can be controlled. You can both mark an item either hidden/visible or you can configure
   rules to determine the visibility at the time the page is displayed for the current user.
4. User placeholders can be used in the URLs of custom menu items. These get replaced at run time with the data of the current user.
5. At any point the customised main menu can be reset, returning it to the default state.

== Dynamic appraisals (T-11185) ==
This feature consists of two main changes:
1. When a user is added or removed from a group (position/organisation/audience) that is assigned to an active appraisal, the
   appraisals assignments will mirror this change, assigning or removing the user from the appraisal instead of the assignments
   being locked on the appraisal's activation.
2. When a user's role (manager/teamlead/appraiser) is changed or deleted mid-appraisal the change will be mirrored in the appraisal
   roles instead of locked on the appraisals activation.

Upgraded sites will have to enable this feature by ticking the "Dynamic Appraisals" checkbox on the "Advanced features" site
administration page. It is enabled by default for new installations.

A more detailed description of the changes can be found here: https://community.totaralms.com/mod/forum/discuss.php?d=9563

== New aggregation and grouping column options in Reportbuilder reports (T-12772) ==
This new feature allows administrators to create new reports that aggregate rows using different functions.
There are also new configurable display options.
Developers need to update 3rd party reports to include data types for each column, otherwise the new display and aggregation options
will not be available in report configuration interface.

== Facetoface course enrolment plugin (T-13092) ==
The plugin is disabled by default. When enabled it can be added to any course as an enrolment instance.
Users not already enrolled in the course may then enrol in a Facetoface session within the course and as part of the process an
enrolment record will be created and they will be given access to the course.

== Facetoface custom fields (T-13718, T-11744) ==
Its now possible to add custom fields to three places in the Facetoface module.
There are significant changes to the session, signup and cancellation screens in order to accommodate these changes.
The following areas now custom fields:
* Sessions - included when adding and editing sessions. Shown on the session signup page.
* Signups - included when the user signs up to a sessions. Shown when viewing the attendees and in the Facetoface session report.
* Cancellations - including when cancelling a signup to a session. Shown when viewing cancellations and in the Facetoface session
  report.

== T-13732  Audience based dashboards ==
Administrators can now create audience based dashboards.
These dashboards operate just like the My Learning page in that a default can be created and then each user in the assigned
audiences can customise their dashboard to suit their needs.
As many dashboards can be created as desired.
The default home page for a Totara site can now be set to a dashboard and users can also be given the option of choosing a particular
dashboard as their home page.

Database schema changes
=======================

New tables:

Bug ID   New table name
-----------------------------
T-10087  totara_navigation
T-11185  appraisal_role_changes
T-12902  facetoface_interest
T-13092  enrol_totara_f2f_pending
T-13502  report_builder_graph
T-13715  totara_navigation_settings
T-13718  facetoface_session_info_field
T-13718  facetoface_session_info_data
T-13718  facetoface_session_info_data_param
T-13718  facetoface_signup_info_field
T-13718  facetoface_signup_info_data
T-13718  facetoface_signup_info_data_param
T-13718  facetoface_cancellation_info_field
T-13718  facetoface_cancellation_info_data
T-13718  facetoface_cancellation_info_data_param

New fields:

Bug ID   Table name                New field name
------------------------------------------------------------
T-11185  appraisal_user_assignment status
T-11185  appraisal_role_assignment timecreated
T-12772  report_builder_columns    transform
T-12772  report_builder_columns    aggregate
T-12772  report_builder_cache      queryhash
T-12892  facetoface_sessions       mincapacity
T-12892  facetoface_sessions       cutoff
T-12900  facetoface_sessions       selfapproval
T-12900  facetoface                selfapprovaltandc
T-12902  facetoface                declareinterest
T-12902  facetoface                interestonlyiffull
T-13195  facetoface_sessions       waitlisteveryone
T-13502  report_builder            timemodified
T-13502  report_builder_saved      timemodified
T-13715  totara_navigation         visibilityold
T-12450  facetoface                allowcancellationsdefault
T-12450  facetoface_sessions       allowcancellations
T-13408  prog_courseset            mincourses
T-13408  prog_courseset            coursesumfield
T-13408  prog_courseset            coursesumfieldtotal
T-13716  facetoface                cancellationscutoffdefault
T-13716  facetoface_sessions       cancellationcutoff

Modified fields:

Bug ID   Table name                Field name
--------------------------------------------------------
T-13718  facetoface_notice_data    data     Changed to text

Dropped tables:

Bug ID   Table name
-------------------------
T-13718  facetoface_session_field
T-13718  facetoface_session_data

Dropped fields:

Bug ID   Table name                Field name
--------------------------------------------------------
T-12772  report_builder_cache      config
T-12772  report_builder_settings   cached value


API changes
===========

== T-9800 Reportbuilder filters can now include the JS they require ==
* Newly introduce rb_filter_type::include_js allows filters to include any JS they require.

== T-11185  New dynamic appraisals advanced feature ==
* totara_assign_core::store_user_assignments - two new optional arguments $newusers (arg 1) and $processor (arg 2), both default to
  null.
* totara_assign_core::get_current_users - new argument $forcegroup (arg 4)
* totara_setup_assigndialogs - one new optional argument (arg 1) The html output of a notice to display on change
* totara_appraisal_renderer::confirm_appraisal_activation - new argument $warnings (arg 1)

== T-12199     New audience based visibility setting for courses, programs and certifications ==
* totara_visibility_where - new optional argument $fieldvisible (arg 3) defaults to course.id.
* rb_base_source::add_course_table_to_joinlist - new optional argument $jointype (arg 4) defaults to LEFT

== T-13104  Facetoface session duration is now stored in seconds instead of minutes ==
* Facetoface_sessions.duration is now stored in seconds instead of in minutes.

== T-13221  Added visibility controls to Appraisal redisplay questions ==
* New method question_base::inherits_permissions; classes extending question_base can now optionally override inherits_permissions
  and return true if the question class should inherit its permissions from another question.
  Initially used for the redisplay question type as it should inherit its permissions from the earlier question it is displaying.

== T-13303  Introduced rb_display_nice_datetime_in_timezone method to complete timezone function suite ==
* new rb_base_source::rb_display_nice_datetime_in_timezone method

== T-13338  Recaptcha login protection has been removed in lieu of the new user lockout support ==
* login_forgot_password_form::captcha_enabled method has been removed. It is no longer used.

== T-13363  Upgraded Totara cron tasks to scheduled tasks ==
The following functions have been removed:
* registration_cron
* totara_core_cron
* block_totara_stats::cron method has been removed.
* reminder_cron
* facetoface_cron
* totara_appraisal_cron
* totara_certification_cron
* tcohort_cron
* totara_cohort_cron
* totara_hierarchy_cron
* totara_message_install
* totara_message_cron
* totara_plan_cron
* totara_program_cron
* totara_reportbuilder_cron

The following files have been removed. If you have cron set up to call any of these you will need to update your cron configuration.
* admin/tool/totara_sync/run_cron.php
* totara/appraisal/cron.php
* totara/appraisal/runcron.php
* totara/certification/cron.php
* totara/cohort/cron.php
* totara/hierarchy/prefix/competency/cron.php
* totara/hierarchy/prefix/goal/cron.php
* totara/message/cron.php
* totara/plan/cron.php
* totara/program/cron.php
* totara/reportbuilder/cron.php
* totara/reportbuilder/runcron.php

== T-13393  New setting to log user out of all sessions when their password is changed ==
* \core\session\manager::kill_user_sessions - new optional argument $keepsid (arg 2) keep the given session id alive. If not
 * provided all sessions belonging to the user will be ended.

== T-13502  Implement graphical reporting in Reportbuilder ==
It is now possible to add graphs to reports. The graphs from each report may be also displayed as page blocks.
Developers need to update 3rd party reports to include date types for each numerical column, otherwise the columns will not be
available when setting up graphs in reportbuidler interface.

== T-13527  Implemented report build date column format ==
This change introduces a new nice date list display class and removes remaining uses of the deprecated rb_display_nice_date function.

== T-13605  added support for Totara specific post upgrade steps ==
It is now possible to define Totara specific post upgrade steps.
These steps should be defined within db/totara_postupgrade.php.
A single method should exist within this file xmldb_pluginname_totara_postupgrade.

== T-13714  New percentage aggregation option in the Reportbuilder ==
* New \totara_reportbuilder\rb\aggregate\percent and \totara_reportbuilder\rb\display\percent classes.
* New boolean datatype for report source columns. Existing columns have been converted where required.
* rb_base_source::rb_display_percent function removed as we have now got dedicated type, aggregate and display for percentages.

== T-13721  User placeholders can now be used in main menu URL's ==
* \totara_core\totara\menu\item::get_url - new optional argument $replaceparams (arg 1) when true (default) params in the URL will
  be replaced with relevant data.

== T-13604  Cleanup Totara user events ==
* \totara_core\event\user_firstlogin was removed, use standard \core\event\user_loggedin
  event instead, in case of first login $USER->firstaccess and $USER->currentlogin are equal.

== T-13604  Cleanup Totara user events ==
* \totara_core\event\user_enrolment was removed, use standard \core\event\user_enrolment_created
  event instead

== T-13711  Non numeric columns are no longer shown as graph data sources in Reportbuilder ==
* rb_base_source::get_used_components - new method that should be overridden to return an array of frankenstyle components used by
  the current source and all parents.
* totara_reportbuilder\rb\aggregate\base::is_graphable - new method that should be overridden by all aggregate classes and should
  return true if the given column can be graphed or false otherwise. By default it runs null and Reportbuilder will guess.
* totara_reportbuilder\rb\display\base::is_graphable - new method that should be overridden by all display classes and should return
  true if the given column can be graphed or false otherwise. By default it runs null and Reportbuilder will guess.
* totara_reportbuilder\rb\transform\base::is_graphable - new method that should be overridden by all transform classes and should
  return true if the given column can be graphed or false otherwise. By default it runs null and Reportbuilder will guess.
* New column option “graphable” can be set to true if the column being defined is graphable.

== T-13789  Remove unused function prog_get_all_users_programs ==
* prog_get_all_users_programs function was removed.

== T-13980  Increase maximum database table name length ==
This change increases the maximum database table name length from 28 to 40.
This facilitates better table naming in Totara.

Other notable changes
=====================

T-12772  Imported SVGGraph 2.16 third party library into Totara
T-13092  New enrol_totara_facetoface plugin added to Totara.
T-13407  New report_usersessions plugin added to Totara.
T-13732  New Totara component totara_dashboard
T-13732  New block_totara_dashboard plugin added to Totara.
T-14081  We strongly advise anyone not already using InnoDB or XtraDB to convert to one of these.
T-14081  InnoDB should be configured to use the Barracuda file format and to use one file per table.

*/