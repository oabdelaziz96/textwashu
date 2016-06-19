var SCRIPT_PROP = PropertiesService.getScriptProperties(); // new property service

function setup() {
    //Define these
    var hubSheetID = "1XjR4WyKNUI9kcqsuv_xWLN0NFERYebXVen8x1GL3Nsk";
    var pollingSheetID = "1azhpnR4ByGA3jlHjexTI8BGH7TPoX6q9Q2eSPOFFKeo";
    var sendTextsID = "1sSCj8daqToaLAoO6f0c2zchTpYFbgmuHPh8lsoyoEvg";
    var archiveID = "1CoeKkvw3Jvn35PKRr8SvRJtlk8c8fTNzWXX5HnWy390";
    var twilioNumber = "+16302503186";
    var twilioSID = "ACaa5fbca66c5a83cdd8439c9090eb8367";
    var twilioAuth = "dcc4859b4c886d1a1cd5453a49a1a8a2";
    
    //Script URL for Reference
    // = https://script.google.com/macros/s/AKfycbw9idxm9BEtnDGkxYKsMaDaor9wTs6LQ3qq66YC6SIvh0GMeS7T/exec
    
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
    
    //Call updatePhoneOnDocs Function
    updatePhoneOnDocs();
}

function updatePhoneOnDocs() {
    var pDoc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("pollingKey"));
    var hDoc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
    
    var numRaw = SCRIPT_PROP.getProperty("twilioNumber");
    var phoneNum = "(" + numRaw.substring(2, 5) + ") " + numRaw.substring(5, 8) + "-" + numRaw.substring(8, 12);
    
    var pSheet = pDoc.getSheetByName("Texts");
    var hSheet = hDoc.getSheetByName("Config");
    
    pSheet.getRange("A1").setValue("Messages -- Text " + phoneNum);
    hSheet.getRange("B3").setValue(phoneNum);
}

//Also need a set up function for import ranges (usually for contacts)