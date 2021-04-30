With this plugin, you can put a price on any course content and ask for a Credit payment to allow access. It works only with "course modules and resources". The support to "course sections" or "topics" is not yet implemented.

## Usage

This works like the [Credit enrol plugin](https://docs.moodle.org/310/en/CreditEnrolment), but instead of restricting the full course, you can restrict individual activities, resources or sections (and you can combine it with other availability conditions, for example, to exclude some group from paying using an "or" restriction set).

## Setup Instructions

Before you can use the availability condtion, you have to do the following steps:

1. Install Course Credit Enrolment (https://moodle.org/plugins/enrol_credit)
1. Create a custom profile field of the type "input field" and name it e.g. "Credits" (can be anything you want)
1. Set the custom profile field to "locked" to avoid that users change the amount of credits themselves
1. Add the credits to that field either via the csv user upload, manually or via a webservice
1. Go to Site administration > Plugins > Enrolments > Manage enrol plugins and make sure Course Credit Enrolments is enabled
1. On the same screen, open the settings of the enrolment method and choose the profile field you've created in the first setting "Profile field mapping".


## Dev Info

- Please, report issues at: https://github.com/stefanscholz/moodle-availability_credit/issues
- Feel free to send pull requests at: https://github.com/stefanscholz/moodle-availability_credit/pulls
