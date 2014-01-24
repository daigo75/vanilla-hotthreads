#Hot Threads Plugin for Vanilla 2.0/2.1b

##Description
Hot Threads displays a list of the Hot Discussions, base on the amount of Views and Comments they received.

The list is displayed in a widget, inside the Panel, and a "Hot" lnk is added to Vanilla Discussions menu.

##Usage
The plugin works out of the box with its default configuration. Changing the settings is, however, very easy.

The following settings are available to configure:

###General
Maximum amount of entries to display. The Hot Threads widget will display this amount of entries.
Page display filter. This filter allows to display the Hot Threads widget only when the User is visiting a certain type of pages.
Auto-Refresh Delay. The Hot Threads Widget can refresh its content automatically. This setting indicates how many seconds the Widget should wait between each refresh. Setting it to zero disables the Auto-Refresh.

###Thresholds
* **Age Threshold**. This value is used to make sure that old Hot Discussions do not keep appearing before newer ones. Whenever a Discussion is older than the specified amount of days, it will get a lower priority in the list.
* **Comment Count Threshold**. This value indicates how many comments a Discussion must have received to be considered "hot". Set it to zero to indicate "any amount".
* **View Count Threshold**. This value indicates how many views a Discussion must have to be considered "hot". Set it to zero to indicate "any amount".

You can find more details about how the Thresholds are processed in Plugin's Settings page.

##Requirements

* PHP 5.3+
* Vanilla 2.0/2.1b (see Notes)

##Notes
Plugin has been tested on Vanilla 2.1b and it should be compatibile with it. However, since Vanilla 2.1 is still in Beta, compatibility cannot be guaranteed. For the same reason, we provide limited assistance for installations in such environment.

##Credits
Thanks to [Zach](http://vanillaforums.org/profile/38268/hgtonight), from Vanilla Community, for the ideas behind this plugin. You can see his work on [his website](http://www.daklutz.com/).
