<?php
ini_set('display_errors',"1");
require_once 'vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $drive_service = new Google_Service_Drive($client);
  $files_list = $drive_service->files->listFiles(array())->getItems();
  echo json_encode($files_list);
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/~oabdelaziz/participation/GoogleApi/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}