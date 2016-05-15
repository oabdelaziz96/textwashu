var SCRIPT_PROP = PropertiesService.getScriptProperties(); // new property service

function setup() {
    //Define these
    var hubSheetID = "1XjR4WyKNUI9kcqsuv_xWLN0NFERYebXVen8x1GL3Nsk";
    var pollingSheetID = "1azhpnR4ByGA3jlHjexTI8BGH7TPoX6q9Q2eSPOFFKeo";
    var twilioNumber = "+13142793219";
    var twilioSID = "AC61c94f6c2b81968a1cf39053629fa3ca";
    var twilioAuth = "d92455e23a3b893288561b88f1b03b31";
    
    //Saving values to script
    SCRIPT_PROP.setProperty("hubKey", hubSheetID);
    SCRIPT_PROP.setProperty("pollingKey", pollingSheetID);
    SCRIPT_PROP.setProperty("twilioNumber", twilioNumber);
    SCRIPT_PROP.setProperty("twilioSID", twilioSID);
    SCRIPT_PROP.setProperty("twilioAuth", twilioAuth);
    
    //Holder property for polling
    SCRIPT_PROP.setProperty("curPollNums", "");
}