Ionize Byzantin Translator module
=======================

Version : 1.0

Ionize version : 0.9.9

Released on january 2013

### About

Byzantin Translator is an Ionize backend module which helps translating the backend.

Byzantin Translator works only with Ionize from version 0.9.9


### Authors

[Michel-Ange Kuntz](http://www.partikule.net)


### Installation

* Copy the folder "Byzantin" into the "/modules" folder of your Ionize installation.
* In the ionize backend, go to : Modules > Administration
* Click on "install"
* Reload the Admin panel (CTRL + R)

Be sure the PHP user has write rights on the folder "/application/language".

### Usage

When you save one file for the lang "xx" : 

* The lang folder "/application/language/xx" is created if it doesn't exists at first new lang save,
* The files you save is added to the folder "/application/language/xx/"
* One backup of the old file is also created, postfixed by the datetime and with the .bak extension



