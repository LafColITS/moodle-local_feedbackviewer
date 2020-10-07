@local @local_feedbackviewer @local_feedbackviewer_teacher
Feature: Show all feedback responses from a user
  In order to review a student's feedback in a course
  As a teacher
  I need to see all the student's responses

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry     | Teacher  | teacher1@example.com |
      | ta1      | Tommy     | TA       | ta1@example.com      |
      | student1 | Sally     | Student  | student1@example.com |
      | student2 | Steve     | Student  | student2@example.com |
      | student3 | Sadie     | Student  | student3@example.com |
      | student4 | Shawn     | Student  | student4@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           | status |
      | teacher1 | C1     | editingteacher | 0      |
      | ta1      | C1     | teacher        | 0      |
      | student1 | C1     | student        | 0      |
      | student2 | C1     | student        | 0      |
      | student3 | C1     | student        | 0      |
      | student4 | C1     | student        | 1      |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | C1     | G1       |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > Groups" in current page administration
    And I add "ta1" user to "Group 1" group members
    And I add "student1" user to "Group 1" group members
    And I add "student2" user to "Group 1" group members
    And the following "activities" exist:
      | activity | name           | course | idnumber  | anonymous |
      | feedback | Football       | C1     | feedback0 | 2         |
      | feedback | Transportation | C1     | feedback1 | 2         |
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
    When I log in as "student1"
    And I am on "Course 1" course homepage
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
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Football"
    And I follow "Answer the questions"
    And I set the field "Favorite football team?" to "Ohio State"
    And I press "Submit your answers"
    And I press "Continue"
    And I follow "Transportation"
    And I follow "Answer the questions"
    And I set the field "Favorite transport mode?" to "Bus"
    And I press "Submit your answers"
    And I log out

  @javascript
  Scenario: View a student's feedback
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Feedback viewer > All feedback" in current page administration
    And I set the field "uid" to "Sally Student"
    Then I should see "Michigan"
    And I should see "Rail"
    And I should not see "Ohio State"
    And I should not see "Bus"
    When I set the field "uid" to "Steve Student"
    Then I should see "Ohio State"
    And I should see "Bus"
    And I should not see "Michigan"
    And I should not see "Rail"
    When I set the field "uid" to "Sadie Student"
    Then I should see "Not completed yet"
    And I log out

  @javascript
  Scenario: Verify that a suspended student's feedback is not shown
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Feedback viewer > All feedback" in current page administration
    Then the "uid" select box should contain "Steve Student"
    And the "uid" select box should not contain "Shawn Student"

  @javascript
  Scenario: Verify that I can only see feedback for groups I have access to
    When I log in as "ta1"
    And I am on "Course 1" course homepage
    And I navigate to "Feedback viewer > All feedback" in current page administration
    Then the "uid" select box should contain "Steve Student"
    And the "uid" select box should not contain "Sadie Student"
