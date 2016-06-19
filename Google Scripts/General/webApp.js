function doGet(e){
    pollResponse(e);
    handleResponse(e);
    return xmlHelper("");
}