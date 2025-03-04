<?php
/* 
  For Password Validation
   - must be 8 Character in length
   - must contain one lowercase and uppercase
   - must contain one number

   
*/

function password_validation($password) {
    // Checks the legnth if password is less than 8
    if(strlen($password) < 8){
        return 'password must be atleast 8 characters long';
    }
    // Checks if the password contains atleast one lowercase
    if(!preg_match('/[a-z]/', $password)){
        return 'password must contain atleast one lowercase and uppercase' ;
    }
     // Checks if the password contains atleast one uppercase
    if(!preg_match('/[A-Z]/', $password)){
        return 'password must contain atleast one lowecase and uppercase';
    }
    
    if(!preg_match('/[0-9]/', $password)){
        return 'password must contain at least one number';
    }

    return true;
}

?>