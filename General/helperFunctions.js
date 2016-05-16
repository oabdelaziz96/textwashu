function xmlHelper(responseText) {
  var beginningText = '<?xml version="1.0" encoding="UTF-8" ?> <Response>';
  var endingText = '</Response>';
  
  var textOutput = beginningText + responseText + endingText;
  var xmlOutput = ContentService.createTextOutput(textOutput).setMimeType(ContentService.MimeType.XML);
  
  return xmlOutput;
}

function addMessage(messageToSend) {
    return '<Message>' + messageToSend + '</Message>'
}

/*
 *Converts 2D array to 1D array with
 * just the first element from each position
 */
function ArrConvert(arrToConvert) {
  var newArr = [];
  
  for(var i = 0; i < arrToConvert.length; i++) {
    newArr[i] = arrToConvert[i][0]+"";
  }
  
  return newArr;
}

/*
 * Must enable URL shortening service under resources -> advanced
 *  Then must enable api under resources -> developers
 */
function URLShortener(longURL) {
  var shortURL = UrlShortener.Url.insert({longUrl: longURL}).id; 
  return shortURL;
}

/*
 *Detects URLs (if available) in text and shortens them
 */
function detectAndShortenURLs(textBlob) {
  textBlob = textBlob.replace(/(<\/Message>)/g, " </Message>");
  var urlRegex = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
  
  var urlArray = textBlob.match(urlRegex);
  
  if (urlArray !== null) {
    for (i=0; i < urlArray.length; i++) {
      var longURL = urlArray[i];
      var shortURL = URLShortener(longURL);
      
      textBlob = textBlob.replace(longURL, shortURL);
    }
  }
  textBlob = textBlob.replace(/( <\/Message>)/g, "</Message>");
  return textBlob;
}

/*
 * Replaces the 3 x's in '['XXX']' with the corresponding value from
 *  the contacts sheet
 */
function mergeInfo(phoneNumber, responseText) {
  var mergeArray = responseText.match(/\[([^\]]+)\]/g);
  
  if (mergeArray !== null) {
    var doc = SpreadsheetApp.openById(SCRIPT_PROP.getProperty("hubKey"));
    var contactSheet = doc.getSheetByName("Contacts");
    var lastContact = contactSheet.getLastRow()+1;
    var contactInfoSize = contactSheet.getLastColumn();
    var contactsArray = contactSheet.getRange(1, 1, lastContact, contactInfoSize).getValues();
    var mergeOptions = contactsArray[0];

    for (i=0; i < mergeArray.length; i++) {
      var curMerge = mergeArray[i];
      var curMergeText = curMerge.substring(1, curMerge.length-1);
      var indexNum = mergeOptions.indexOf(curMergeText);
      
      if (indexNum >= 0) {
        //Find the merge value by number and replace it with curMerge
        var rowOfPhoneNum = ArrConvert(contactsArray).indexOf(phoneNumber+"");
        var mergeValue = contactsArray[rowOfPhoneNum][indexNum]+"";
        responseText = responseText.replace(curMerge, mergeValue);
      }
    }
  }
  return responseText;
}

/*
 * Removes hashtags from text blob
 */
function removeTags(textBlob) {
  var regex = /(?=#)[^ ]*/g;
  textBlob = textBlob.replace(regex, "");
  textBlob = textBlob.trim();
  return textBlob;
}