function xmlHelper(responseText) {
  var beginningText = '<?xml version="1.0" encoding="UTF-8" ?> <Response> <Message>';
  var endingText = '</Message> </Response>';
  
  var textOutput = beginningText + responseText + endingText;
  var xmlOutput = ContentService.createTextOutput(textOutput).setMimeType(ContentService.MimeType.XML);
  
  return xmlOutput;
}