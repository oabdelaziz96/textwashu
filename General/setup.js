var SCRIPT_PROP = PropertiesService.getScriptProperties(); // new property service

function setup() {
    //Define these
    var hubSheetID = "1XjR4WyKNUI9kcqsuv_xWLN0NFERYebXVen8x1GL3Nsk";
    var pollingSheetID = "1azhpnR4ByGA3jlHjexTI8BGH7TPoX6q9Q2eSPOFFKeo";
    var sendTextsID = "1sSCj8daqToaLAoO6f0c2zchTpYFbgmuHPh8lsoyoEvg";
    var twilioNumber = "+13142549679";
    var twilioSID = "AC994903a8a48275a31f0a5c8e9dd50824";
    var twilioAuth = "9699824d4e98e2bf49b3827049007ce9";
    
    //Saving values to script
    SCRIPT_PROP.setProperty("hubKey", hubSheetID);
    SCRIPT_PROP.setProperty("pollingKey", pollingSheetID);
    SCRIPT_PROP.setProperty("sendTextsKey", sendTextsID);
    SCRIPT_PROP.setProperty("twilioNumber", twilioNumber);
    SCRIPT_PROP.setProperty("twilioSID", twilioSID);
    SCRIPT_PROP.setProperty("twilioAuth", twilioAuth);
    
    //Holder property for polling
    SCRIPT_PROP.setProperty("curPollNums", "");
}