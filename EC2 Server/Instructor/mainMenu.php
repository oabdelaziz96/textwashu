<?php
require "restrictedArea.php";
?>
<title>Main Menu</title>
<H1 align="center">Main Menu</H1>

<style>
.button {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
}

.button1 {width: 250px;}
.button2 {width: 50%;}
.button3 {width: 100%;}
</style>

<a href="liveCrossLogin.php?location=texts"><button class="button button3">Live Texts</button></a><br>
<a href="liveCrossLogin.php?location=poll"><button class="button button3">Live Poll</button></a><br>
<a href="manageHashtags.php"><button class="button button3">Manage Hashtags</button></a><br>
<a href="http://www.google.com"><button class="button button3">Administer a Quiz Session</button></a><br><!--Not implemented yet-->
<a href="viewContacts.php"><button class="button button3">Manage Contacts</button></a><br>
<a href="viewAllMessages.php"><button class="button button3">View All Messages</button></a><br>
<a href="http://www.google.com"><button class="button button3">Send Message</button></a><br><!--Not implemented yet-->
<a href="managePreferences.php"><button class="button button3">Preferences</button></a><br>