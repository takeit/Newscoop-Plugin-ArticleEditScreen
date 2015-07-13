# Installation
These installation steps were tested on an Ubuntu 14.04 server on Amazon EC2. They should work for any *nix based host, but permissions might be different.
Please add specific steps for your platform.

## Before you start:
The Composer installation requires a lot of RAM. If installation is hanging or failing, you might be running out of memory.
One possible solution for Linux servers is to enable swap. Instructions for Amazon EC2 instances here: http://stackoverflow.com/questions/17173972/how-do-you-add-swap-to-an-ec2-instance

Scoopwriter requires Newscoop version 4.4.3. Make sure you have upgraded, and that the installation is working before proceeding with the install.

1.  In the admin (backend) interface, navigate to `Plugins -> Manage Plugins`.
2.  Type `Scoopwriter` in the search field and select the plugin. This will list your next installation steps.
3.  Open a terminal window on your Newscoop server or connect an ssh session.
4.  Run the commands listed on the _Manage Plugins_ page. Paths will reflect your server environment, so simply copy & paste.
    It is important that your installed files have the right permissions for Newscoop to work properly. How to do this can depend on your OS and Newscoop installation.
    On Ubuntu, run step 2 as root (`sudo`) and step 3 as www-data (`sudo -u www-data`).
5.  Clear the Newscoop cache by deleting the contents of the cache folder (`sudo rm -rf cache/*` in the newscoop directory)
6.  Refresh the _Manage Plugins_ page. Scoopwriter should now be listed. Enable it.
7.  Go to `Plugins -> Scoopwriter -> Permissions` and enable the checkbox for all users who should work with the Scoopwriter plugin.
_Note that users must have an author asigned to be listed here._ If a user isn't listed, go to `Users -> Manage Users`, edit the user and select an author from the author dropdown menu.
8.  Scoopwriter should now be enabled whenever you create or edit an article. Enjoy :)
