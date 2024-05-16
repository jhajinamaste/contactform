<?php
session_start();
include('db.php');

$msg = '';
$color = '';
$adminEmail = "jhajinamaste@gmail.com"; // REPLACE WITH ADMIN EMAIL ID

$name    = (isset($_POST['name']))?$_POST['name']:'';
$phone   = (isset($_POST['phone']))?$_POST['phone']:'';
$email   = (isset($_POST['email']))?$_POST['email']:'';
$subject = (isset($_POST['subject']))?$_POST['subject']:'';
$message = (isset($_POST['message']))?$_POST['message']:'';

function checkEmpty($var){
  if($var == '' || empty($var)){
    return true;
  }
  return false;
}

function specialChars($str) {
  return preg_match('/[^a-zA-Z0-9]/', $str) > 0;
}

if(isset($_POST['submit'])){
  $color = 'error';
 
  if(empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) != 0){ 
		$msg = "Captcha code is incorrect";
	}else{
    if(checkEmpty($name)){
      $msg = "Name is required";
    }else if(preg_match('/[\'^£$%&*()}{@!#~?><>,|=_+¬-]/', $name)){
      $msg = "Name cannot contain any special characters";
    }else if(preg_match('~[0-9]+~', $name)){
      $msg = "Name cannot contain any numbers";
    }else if(checkEmpty($phone)){
      $msg = "Phone number is required";
    }else if(!ctype_digit($phone)){
      $msg = "Phone number can only contain numeric values [0-9]";
    }else if(strlen($phone) < 10){
      $msg = "Phone number must be of 10 digits";
    }else if(checkEmpty($email)){
      $msg = "Email address is required";
    }else if(checkEmpty($subject)){
      $msg = "Subject is required";
    }else if(checkEmpty($message)){
      $msg = "Message is required";
    }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $msg = "Please enter a valid email address";
    }else{
      $ip = $_SERVER['REMOTE_ADDR'];
  
      $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM contact_form WHERE phone = :phone AND email = :email AND subject = :sub AND message = :msg AND ip_address = :ip");
      $stmt-> bindValue(':phone', $phone);
      $stmt-> bindValue(':email', $email);
      $stmt-> bindValue(':sub', $subject);
      $stmt-> bindValue(':msg', $message);
      $stmt-> bindValue(':ip', $ip);
      $stmt-> execute();
      $f = $stmt->fetch(PDO::FETCH_ASSOC);
  
      if($f['cnt'] > 0){
        $msg = "Duplicate submission detected. You have already submitted your query.";
      }else{
        $stmt = $db->prepare("INSERT INTO contact_form(`name`, `phone`, `email`, `subject`, `message`, `ip_address`)VALUES(:name, :phone, :email, :sub, :msg, :ip)");
        $stmt-> bindValue(':name', $name);
        $stmt-> bindValue(':phone', $phone);
        $stmt-> bindValue(':email', $email);
        $stmt-> bindValue(':sub', $subject);
        $stmt-> bindValue(':msg', $message);
        $stmt-> bindValue(':ip', $ip);
        $stmt-> execute();
    
        if($stmt){
          $color = "success";
          $msg = "Success! Form submitted successfully.";
          
          // SEND MAIL
          $content = "You have received a new enquiry.<br><br>
                      Name: ".$name."<br>
                      Phone:  ".$phone."<br>
                      Email: ".$email."<br>
                      Subject: ".$subject."<br>
                      Messsage: ".$message;
  
          $headers = "MIME-Version: 1.0" . "\r\n";
          $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
          $headers .= 'From: <'.$email.'>' . "\r\n";
  
          mail($adminEmail, "Enquiry Received", $content, $headers);
        }else{
          $msg = "Server Error! Please refresh the page and try again.";
        }
      }
    }
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Form</title>
  <link rel="stylesheet" href="app.css">
  <script type='text/javascript'>
    function refreshCaptcha(){
      var img = document.images['captchaimg'];
      img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
    }
  </script>
</head>
<body>
  <div class="center">
    <h1>Contact Us</h1>
    <div class="msg <?php echo $color; ?>"><?php echo $msg; ?></div>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
      <table>
        <tr>
          <th>Full Name</th>
          <td><input type="text" name="name" value="<?php echo $name; ?>"></td>
        </tr>
        <tr>
          <th>Phone</th>
          <td><input type="text" name="phone" maxlength="10" value="<?php echo $phone; ?>"></td>
        </tr>
        <tr>
          <th>Email</th>
          <td><input type="text" name="email" value="<?php echo $email; ?>"></td>
        </tr>
        <tr>
          <th>Subject</th>
          <td><input type="text" name="subject" value="<?php echo $subject; ?>"></td>
        </tr>
        <tr>
          <th>Message</th>
          <td><textarea name="message" cols="30" rows="10"><?php echo $message; ?></textarea></td>
        </tr>
        <tr>
          <th>
            <img src="captcha.php?rand=<?php echo rand(); ?>" id='captchaimg'>
            <small><a href='javascript: refreshCaptcha();'>Refresh Image</a></small>
          </th>
          <td><input type="text" name="captcha_code" placeholder="Enter code"></td>
        </tr>
        <tr>
          <th></th>
          <td><input type="submit" value="Submit" name="submit"></td>
        </tr>
      </table>
    </form>
  </div>
</body>
</html>