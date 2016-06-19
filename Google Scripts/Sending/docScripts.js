/*
 * Must enable URL shortening service under resources -> advanced
 *  Then must enable api under resources -> developers
 *  Otherwise go to hub and turn URL shortening off
 */

function onOpen(e) {
   SpreadsheetApp.getUi()
       .createMenu('Texting Options')
       .addItem('Send Texts', 'GenScripts.sendingSendAllSMS')
       .addItem('Send MMS', 'GenScripts.sendingSendAllMMS')
       .addItem('Load Contacts', 'GenScripts.sendingLoadAll')
       .addItem('Load Filter', 'GenScripts.sendingLoadFilter')
       .addItem('Clear List', 'removeCurrent')
       .addToUi();
 }

function removeCurrent() {
   var ss = SpreadsheetApp.getActiveSheet();
   GenScripts.sendingRemoveCurrent();
   ss.setActiveSelection("A1");
}

