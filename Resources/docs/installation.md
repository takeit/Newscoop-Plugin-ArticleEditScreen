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
4.  Navigate to your Newscoop directory in the terminal.
5.  Run `sudo php application/console plugins:install "newscoop/scoopwriter"`.
6.  Run `sudo -u www-data php application/console assets:install public/`.
    Using `sudo -u www-data` ensures the files have correct permissions.
7.  Run `sudo rm -rf cache/*` to delete the Newscoop cache.
8.  Refresh the _Manage Plugins_ page. Scoopwriter should now be listed. Enable it.
9.  Go to `Plugins -> Scoopwriter -> Permissions` and enable the checkbox for all users who should work with the Scoopwriter plugin.
_Note that users must have an author asigned to be listed here._ If a user isn't listed, go to `Users -> Manage Users`, edit the user and select an author from the author dropdown menu.
10.  Scoopwriter should now be enabled whenever you create or edit an article. Enjoy :)
