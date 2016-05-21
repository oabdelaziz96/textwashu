var SCRIPT_PROP = PropertiesService.getScriptProperties(); // new property service

function setup() {
    //Define these
    var hubSheetID = "1XjR4WyKNUI9kcqsuv_xWLN0NFERYebXVen8x1GL3Nsk";
    var pollingSheetID = "1azhpnR4ByGA3jlHjexTI8BGH7TPoX6q9Q2eSPOFFKeo";
    var sendTextsID = "1sSCj8daqToaLAoO6f0c2zchTpYFbgmuHPh8lsoyoEvg";
    var archiveID = "1CoeKkvw3Jvn35PKRr8SvRJtlk8c8fTNzWXX5HnWy390";
    var twilioNumber = "+16309488643";
    var twilioSID = "AC3aa2b5eadf97d7dd03129baa01173685";
    var twilioAuth = "2abe61168a8dc62f8d87daf3f4c52651";
    
    //Saving values to script
    SCRIPT_PROP.setProperty("hubKey", hubSheetID);
    SCRIPT_PROP.setProperty("pollingKey", pollingSheetID);
    SCRIPT_PROP.setProperty("sendTextsKey", sendTextsID);
    SCRIPT_PROP.setProperty("archiveKey", archiveID);
    SCRIPT_PROP.setProperty("twilioNumber", twilioNumber);
    SCRIPT_PROP.setProperty("twilioSID", twilioSID);
    SCRIPT_PROP.setProperty("twilioAuth", twilioAuth);
    
    //Holder property for polling
    SCRIPT_PROP.setProperty("curPollNums", "");
}