function xmlHelper(responseText) {
  var beginningText = '<?xml version="1.0" encoding="UTF-8" ?> <Response> <Message>';
  var endingText = '</Message> </Response>';
  
  var textOutput = beginningText + responseText + endingText;
  var xmlOutput = ContentService.createTextOutput(textOutput).setMimeType(ContentService.MimeType.XML);
  
  return xmlOutput;
}


/*
 *Converts 2D array to 1D array with
 * just the first element from each position
 */
function ArrConvert(arrToConvert) {
  var newArr = [];
  
  for(var i = 0; i < arrToConvert.length; i++) {
    newArr[i] = arrToConvert[i][0];
  }
  
  return newArr;
}

/*
 * Must enable URL shortening service under resources -> advanced
 *  Then must enable api under resources -> developers
 */
function URLShortner(longURL) {
  var shortURL = UrlShortener.Url.insert({longUrl: longURL}).id; 
  return shortURL;
}