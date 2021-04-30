Credit Availability Condition for Moodle
----------------------------------------

With this plugins, you can put a price in any course content and ask for a Credit payment to allow access.

The person in charge to configure the enrolment method on the course will be able to configure the enrolment cost's value and currency.

The user will be able to pay in some other currency at Credit website. The conversion rates will be calculated and applied by Credit.

It works only with "course modules and resources". The support to "course sections" or "topics" is not yet implemented.

Install
-------

* Put these files at moodle/availability/condition/credit/
 * You may use composer
 * or git clone
 * or download the latest version from https://github.com/danielneis/moodle-availability_credit/archive/master.zip
* Log in your Moodle as Admin and go to "Notifications" page
* Follow the instructions to install the plugin
* You must activate the IPN at your Credit account
* You must also use HTTPS on your Moodle site

Usage
-----

This works like the [Credit enrol plugin](https://docs.moodle.org/en/Paypal_enrolment), but instead of restricting the full course, you can restrict individual activities, resources or sections (and you can combine it with other availability conditions, for example, to exclude some group from paying using an "or" restriction set).

For each restriction you add, you can set a business email address, cost, currency, item name and item number.

Funding
-------

The development of this plugin was funded by TRREE - TRAINING AND RESOURCES IN RESEARCH ETHICS EVALUATION - http://www.trree.org/

Dev Info
--------

Please, report issues at: https://github.com/danielneis/moodle-availability_credit/issues

Feel free to send pull requests at: https://github.com/danielneis/moodle-availability_credit/pulls

[![Travis-CI Build Status](https://travis-ci.org/danielneis/moodle-availability_credit.svg?branch=master)](https://travis-ci.org/danielneis/moodle-availability_credit)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/danielneis/moodle-availability_credit/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/danielneis/moodle-availability_credit/?branch=master)
