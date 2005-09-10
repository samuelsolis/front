$Id$

Introduction:

This module is for people who want a custom front page to their Drupal sites.

The key functionality of this module is outlined below. I recommend drupal site administrators 
look at SECTIONS.MODULE if you are looking to vary the theme/look of different sections of your site.

This module is intended for Drupal version 4.5 and later versions.


Key Functionality:

1. Allows you to specify a custom front page to your site for visitors who are not-logged in (Anonymous Users) 
and an alternative front page to your site for users who are already logged in (Authenticatd Users).

3. Allows you to have a FULL front page or SPLASH page - a completely different layout to your 
main drupal site - or alternatively as a THEMED full page, i.e. it loads with your default drupal site theme & layout.

4. Allows you to automatically REDIRECT users to a specific page or path.

5. You can include PHP/HTML or both in your frontpage


######### (update Sept. 10th 2005) #########

6. Updated to allow you to override the HOME links on your site including the breadcrumb HOME links. 
   Useful if you have a splash page or flash intro on your site and when people click on HOME from within
   your site they don't go back to that.

############################################




Installation:

1. Upload front_page.module to your MODULES folder.

2. Go to the ADMINISTER section of your drupal site.

3. Click on MODULES

4. Enable the front_page.module

5. Go to ADMINISTER - SETTINGS - FRONT_PAGE

6. Paste in the TEXT/HTML/PHP contents of your front pages. 

7. To test your front_pages, use the following link http://www.example.com/?q=front_page

8. Once you are happy with your front_pages, set the DEFAULT FRONT PAGE setting to be front_page. 
   You can do this at the bottom of the front_page configuration page (ADMINISTER -> SETTINGS -> FRONT_PAGE)
   or under ADMINISTER -> SETTINGS.

   
Hope you find it useful. 

This module has been developed by a few members of the drupal community and we're always looking for ideas for improving it. Email me or post a message on the drupal.org site if you have any ideas on how we can improve the module. 

Dublin Drupaller


dub@m3m.eu.com