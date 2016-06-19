function doGet(e){
    try {
        
        return xmlHelper("");
        
    } finally {
        
        pollResponse(e);
        hubResponse(e);    
    
    }
}

/*
 * Old function
 
function doGet(e){
    pollResponse(e);
    return handleResponse(e);
}
 
 
 */