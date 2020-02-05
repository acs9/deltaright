<?PHP
######################################################
#                                                    #
#             Forms To Go Lite  4.5.4                #
#             http://www.bebosoft.com/               #
#                                                    #
######################################################




define('kOptional', true);
define('kMandatory', false);

define('kStringRangeFrom', 1);
define('kStringRangeTo', 2);
define('kStringRangeBetween', 3);
        
define('kYes', 'yes');
define('kNo', 'no');




error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('track_errors', true);

function DoStripSlashes($fieldValue)  { 
// temporary fix for PHP6 compatibility - magic quotes deprecated in PHP6
 if ( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc() ) { 
  if (is_array($fieldValue) ) { 
   return array_map('DoStripSlashes', $fieldValue); 
  } else { 
   return trim(stripslashes($fieldValue)); 
  } 
 } else { 
  return $fieldValue; 
 } 
}

function FilterCChars($theString) {
 return preg_replace('/[\x00-\x1F]/', '', $theString);
}

function CheckString($value, $low, $high, $mode, $limitAlpha, $limitNumbers, $limitEmptySpaces, $limitExtraChars, $optional) {

 $regEx = '';

 if ($limitAlpha == kYes) {
  $regExp = 'A-Za-z';
 }
 
 if ($limitNumbers == kYes) {
  $regExp .= '0-9'; 
 }
 
 if ($limitEmptySpaces == kYes) {
  $regExp .= ' '; 
 }

 if (strlen($limitExtraChars) > 0) {
 
  $search = array('\\', '[', ']', '-', '$', '.', '*', '(', ')', '?', '+', '^', '{', '}', '|', '/');
  $replace = array('\\\\', '\[', '\]', '\-', '\$', '\.', '\*', '\(', '\)', '\?', '\+', '\^', '\{', '\}', '\|', '\/');

  $regExp .= str_replace($search, $replace, $limitExtraChars);

 }

 if ( (strlen($regExp) > 0) && (strlen($value) > 0) ){
  if (preg_match('/[^' . $regExp . ']/', $value)) {
   return false;
  }
 }

 if ( (strlen($value) == 0) && ($optional === kOptional) ) {
  return true;
 } elseif ( (strlen($value) >= $low) && ($mode == kStringRangeFrom) ) {
  return true;
 } elseif ( (strlen($value) <= $high) && ($mode == kStringRangeTo) ) {
  return true;
 } elseif ( (strlen($value) >= $low) && (strlen($value) <= $high) && ($mode == kStringRangeBetween) ) {
  return true;
 } else {
  return false;
 }

}


function CheckEmail($email, $optional) {
 if ( (strlen($email) == 0) && ($optional === kOptional) ) {
  return true;
  } elseif ( preg_match("/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i", $email) == 1 ) {
  return true;
 } else {
  return false;
 }
}




if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
 $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
 $clientIP = $_SERVER['REMOTE_ADDR'];
}

$FTGemail = DoStripSlashes( $_POST['email'] );
$FTGform_tools_initialize_form = DoStripSlashes( $_POST['form_tools_initialize_form'] );
$FTGform_tools_form_id = DoStripSlashes( $_POST['form_tools_form_id'] );
$FTGname = DoStripSlashes( $_POST['name'] );
$FTGphone = DoStripSlashes( $_POST['phone'] );
$FTGmessage = DoStripSlashes( $_POST['message'] );



$validationFailed = false;

# Fields Validations


if (!CheckEmail($FTGemail, kMandatory)) {
 $FTGErrorMessage['email'] = '';
 $validationFailed = true;
}

if (!CheckString($FTGname, 3, 255, kStringRangeBetween, kYes, kNo, kNo, '', kMandatory)) {
 $FTGErrorMessage['name'] = '';
 $validationFailed = true;
}

if (!CheckString($FTGphone, 10, 14, kStringRangeBetween, kNo, kNo, kNo, '', kMandatory)) {
 $FTGErrorMessage['phone'] = '';
 $validationFailed = true;
}

if (!CheckString($FTGmessage, 4, 1000, kStringRangeBetween, kNo, kNo, kNo, '', kMandatory)) {
 $FTGErrorMessage['message'] = '';
 $validationFailed = true;
}



# Include message in error page and dump it to the browser

if ($validationFailed === true) {

 $errorPage = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>Error</title></head><body>Errors found: <!--VALIDATIONERROR--></body></html>';

 $errorPage = str_replace('<!--FIELDVALUE:email-->', $FTGemail, $errorPage);
 $errorPage = str_replace('<!--FIELDVALUE:form_tools_initialize_form-->', $FTGform_tools_initialize_form, $errorPage);
 $errorPage = str_replace('<!--FIELDVALUE:form_tools_form_id-->', $FTGform_tools_form_id, $errorPage);
 $errorPage = str_replace('<!--FIELDVALUE:name-->', $FTGname, $errorPage);
 $errorPage = str_replace('<!--FIELDVALUE:phone-->', $FTGphone, $errorPage);
 $errorPage = str_replace('<!--FIELDVALUE:message-->', $FTGmessage, $errorPage);


 $errorList = @implode("<br />\n", $FTGErrorMessage);
 $errorPage = str_replace('<!--VALIDATIONERROR-->', $errorList, $errorPage);



 echo $errorPage;

}

if ( $validationFailed === false ) {

 # Email to Form Owner
  
 $emailSubject = FilterCChars("Enquiry request from $FTGname");
  
 $emailBody = "email : $FTGemail\n"
  . "name : $FTGname\n"
  . "phone : $FTGphone\n"
  . "message : $FTGmessage\n"
  . "--\n"
  . "\n"
  . "";
  $emailTo = 'diptanshu@msn.com';
   
  $emailFrom = FilterCChars("$FTGemail");
   
  $emailHeader = "From: $emailFrom\n"
   . "MIME-Version: 1.0\n"
   . "Content-type: text/plain; charset=\"UTF-8\"\n"
   . "Content-transfer-encoding: 8bit\n";
   
  mail($emailTo, $emailSubject, $emailBody, $emailHeader);
  
  
# Include message in the success page and dump it to the browser

$successPage = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>Success</title></head><body>Form submitted successfully. It will be reviewed soon.</body></html>';

$successPage = str_replace('<!--FIELDVALUE:email-->', $FTGemail, $successPage);
$successPage = str_replace('<!--FIELDVALUE:form_tools_initialize_form-->', $FTGform_tools_initialize_form, $successPage);
$successPage = str_replace('<!--FIELDVALUE:form_tools_form_id-->', $FTGform_tools_form_id, $successPage);
$successPage = str_replace('<!--FIELDVALUE:name-->', $FTGname, $successPage);
$successPage = str_replace('<!--FIELDVALUE:phone-->', $FTGphone, $successPage);
$successPage = str_replace('<!--FIELDVALUE:message-->', $FTGmessage, $successPage);

echo $successPage;

}

?>