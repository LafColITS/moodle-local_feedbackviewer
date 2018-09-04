@local @local_feedbackviewer
Feature: Show my feedback responses in a course
  In order to review my feedback responses in a course
  As a student
  I need to see all my feedback

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Terry | Teacher | teacher1@example.com |
      | student1 | Sally | Student | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity | name           | course | idnumber  | anonymous |
      | feedback | Football       | C1     | feedback0 | 2         |
      | feedback | Transportation | C1     | feedback1 | 2         |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I follow "Football"
    And I click on "Edit questions" "link" in the "[role=main]" "css_element"
    And I add a "Longer text answer" question to the feedback with:
      | Question | Favorite football team? |
      | Label    | footballteam            |
    And I am on "Course 1" course homepage
    And I follow "Transportation"
    And I click on "Edit questions" "link" in the "[role=main]" "css_element"
    And I add a "Longer text answer" question to the feedback with:
      | Question | Favorite transport mode? |
      | Label    | transportmode            |
    And I log out

  @javascript
  Scenario: View a student's feedback
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "Feedback viewer > My feedback" in current page administration
    And I should see "Not completed yet"
    And I follow "Course 1"
    And I follow "Football"
    And I follow "Answer the questions"
    And I set the field "Favorite football team?" to "Michigan"
    And I press "Submit your answers"
    And I press "Continue"
    And I follow "Transportation"
    And I follow "Answer the questions"
    And I set the field "Favorite transport mode?" to "Rail"
    And I press "Submit your answers"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "Feedback viewer > My feedback" in current page administration
    Then I should see "Michigan"
    And I should see "Rail"
    And I log out
