<?php 
  //function for reading the file
function fileReader(){
  $files=array();
    $file=fopen('files/serverfile.txt','r+');
    $count=0;
    while(!feof($file)){
      $files[$count]=fgets($file);
      $count++;
    }
    fclose($file);
    return $files;
}
function ideas($bideas,$conn){
  $available_money=picLoanAndCash($conn);
    $amount_available=$available_money[0];
    $half=($amount_available/2);
    for ($i=0; $i < sizeof($bideas); $i++) { 
      $bidea_part=explode(" ",$bideas[$i]);
      $amount=$bidea_part[2];
      if ($amount>$half) {
        cutCommand($bideas[$i]);
      }
    }
}
#function for login authentication
function login($username,$password,$conn){
  $sql="SELECT * FROM member WHERE username='$username' AND password='$password' ";
  $result=$conn->query($sql);
  if ($conn->affected_rows==1) {
    $row=$result->fetch_assoc();
    return $row;
  }else {
    return "login failed";
  }
  $conn->close();
}
#function for registering a new member
function register($fname,$lname,$username,$password,$initial_deposit,$date_of_join,$position,$conn){
  // $sql="INSERT INTO member(id,fname,lname,username,password,initial_deposit,date_of_join,position)
  // VALUES(NULL,$fname,$lname,$username,$password,$initial_deposit,$date_of_join,$position)";
  $sql="INSERT INTO `member` (`id`, `fname`, `lname`, `username`, `password`, `initial_deposit`, `date_of_join`, `position`) VALUES (NULL, '$fname', '$lname', '$username', '$password', '$initial_deposit', '$date_of_join', '$position')";
  $insert=$conn->query($sql);

  if ($insert==1) {
    return $insert;
  }else {
    // echo "Error: " . $sql . "<br>" . $conn->error;
    return "insertion error";
  }
  $conn->close();
}
#function to check if the user name is already taken
function checkuser($username,$conn){
  $sql="SELECT * FROM member WHERE username='$username'";
  $result=$conn->query($sql);
  if ($conn->affected_rows!=0) {
    return "already taken";
  }else {
    return "not taken";
  }
  $conn->close();
}
/*
*function for checking if the user exists
*/
function verifyUser($username,$conn){
  $sql="SELECT * FROM member WHERE username='$username'";
  $result=$conn->query($sql);
  if ($conn->affected_rows!=0) {
    return "user exists";
  }else {
    return "user unknown";
  }
  $conn->close();
}
/*
*function for inserting the contributions data
*/
function handleContribution($amount,$newdate,$username,$conn){
 
  $sql="INSERT INTO `contribution` (`id`, `amount`, `date`, `username`) VALUES (NULL, '$amount', '$newdate', '$username')";
  // $sql="INSERT INTO 'contribution'('id','amount','date','username') VALUES(NULL,'$amount','$newdate','$username')";
  $result=$conn->query($sql);
  if ($result==1) {
    return 'success';
  }else{
    return 'insertion failed';
  }
  $conn->close();
}
/*
*function for deleting a command from the file
*/
function cutCommand($command){
  $data=file("files/serverfile.txt");
  $out=array();
  foreach ($data as $line) {
    if(trim($line)!=$command){
      $out[]=$line;
    }
  }
  $open_file=fopen("files/serverfile.txt","w+");
  flock($open_file,LOCK_EX);
  foreach($out as $line){
     fwrite($open_file,$line);
  }
  flock($open_file,LOCK_UN);
  fclose($open_file);
  $_SESSION['pending']=fileReader();
  // var_dump($_SESSION['pending']);
}
/*
*function for inserting loan details
*/
function handleLoan($amount,$date_of_issue,$balance,$status,$username,$conn){
  $sql="INSERT INTO `loan` (`id`, `amount`, `date_of_issue`, `balance`, `status`, `username`) VALUES (NULL, '$amount', '$date_of_issue', '$balance', '$status', '$username')";
  $result=$conn->query($sql);
  if ($result==1) {
    return 'success';
  }else{
    return 'insertion failed';
  }
  $conn->close();
}
/*
*function for inserting new investment idea details
*/
function handleIdea($idea,$initial_amount,$date_of_approval,$profits,$losses,$username,$conn){
  $sql="INSERT INTO `investment` (`id`, `idea`, `initial_amount`, `date_of_approval`, `profits`, `losses`, `username`) VALUES (NULL, '$idea', '$initial_amount', '$date_of_approval', '$profits', '$losses', '$username')";
    $result=$conn->query($sql);
    if ($result==1) {
      return 'success';
    }else{
      return 'insertion failed';
    }
    $conn->close();
}
/*
*function for updating investment idea details
*/
function updateIdea($idea,$profits,$losses,$conn){
  $sql1="SELECT * FROM investment WHERE idea='$idea'";
  $result1=$conn->query($sql1);
  while($i=$result1->fetch_assoc()){
    $available_profits=$i['profits'];
    $available_losses=$i['losses'];
  }
  $new_profits=$available_profits+$profits;
  $new_losses=$available_losses+$losses;

  $sql="UPDATE investment SET profits='$new_profits', losses='$new_losses' WHERE idea='$idea' ";
  $result=$conn->query($sql);
    if ($result==1) {
      return 'success';
    }else{
      return 'update failed';
    }
    $conn->close();
}
/*
*function for checking if the business idea exists
*/
function checkForIdea($idea,$conn){
  $sql="SELECT * FROM investment WHERE idea='$idea'";
  $result=$conn->query($sql);
  if ($conn->affected_rows!=0) {
    return "idea exists";
  }else {
    return "idea unknown";
  }
  $conn->close();
}
/*
*function for handeling business idea votes
*/
function handleVote($deal,$username,$command,$conn){
  if ($deal=='approve') {
    $vote_exists=checkVote1($username,$command,$conn);
    if ($vote_exists=='vote unavailable') {
      $rejection_vote=checkVote2($username,$command,$conn);
      if ($rejection_vote=='vote exists') {
        $deleter="DELETE FROM votes WHERE rejections='$username' AND idea='$command'";
        $deleted=$conn->query($deleter);
        $sql="INSERT INTO votes(id,approvals,rejections,idea) VALUES(NULL,'$username',NULL,'$command')";
        $result=$conn->query($sql);
        if ($result==1) {
          return 'success';
        }else{
          return 'insertion failed';
        }
        $conn->close();
      }else{
        $sql="INSERT INTO votes(id,approvals,rejections,idea) VALUES(NULL,'$username',NULL,'$command')";
        $result=$conn->query($sql);
        if ($result==1) {
          return 'success';
        }else{
          return 'insertion failed';
        }
        $conn->close();
      }
    }else {
      return 'already approved';
    }
  }elseif ($deal=='reject') {
    $vote_exists=checkVote2($username,$command,$conn);
    if ($vote_exists=='vote unavailable') {
      $approval_vote=checkVote1($username,$command,$conn);
      if ($approval_vote=='vote exists') {
        $deleter="DELETE FROM votes WHERE approvals='$username' AND idea='$command'";
        $deleted=$conn->query($deleter);
        $sql="INSERT INTO votes(id,approvals,rejections,idea) VALUES(NULL,NULL,'$username','$command')";
        $result=$conn->query($sql);
        if ($result==1) {
          return 'success';
        }else{
          return 'insertion failed';
        }
        $conn->close();
      }else{
        $sql="INSERT INTO votes(id,approvals,rejections,idea) VALUES(NULL,NULL,'$username','$command')";
        $result=$conn->query($sql);
        if ($result==1) {
          return 'success';
        }else{
          return 'insertion failed';
        }
        $conn->close();
      }
    }else {
      return 'already rejected';
    }
  }
}
/*
*functions for checking if the vote already exists
*/
#checking approvals
 function checkVote1($username,$command,$conn){
  $sql="SELECT * FROM votes WHERE approvals='$username' AND idea='$command'";
  $result=$conn->query($sql);
  if ($conn->affected_rows!=0) {
    return "vote exists";
  }else {
    return "vote unavailable";
  }
  $conn->close();
}
#checking rejections
function checkVote2($username,$command,$conn){
  $sql="SELECT * FROM votes WHERE rejections='$username' AND idea='$command'";
  $result=$conn->query($sql);
  if ($conn->affected_rows!=0) {
    return "vote exists";
  }else {
    return "vote unavailable";
  }
  $conn->close();
}
/*
*function for counting votes
*/
function countVotes($command,$value,$conn){
  if ($value=='likes') {
    $sql="SELECT approvals FROM votes where idea='$command' AND approvals IS NOT NULL";
    $result=$conn->query($sql);
    $num=$conn->affected_rows;
    return $num;
  }elseif($value=='dislikes'){
    $sql="SELECT rejections FROM votes where idea='$command' AND rejections IS NOT NULL";
    
    $result=$conn->query($sql);
    $num=$conn->affected_rows;
    return $num;
  }
  
}
/*
*function for updating votes
*/
function updateVote($idea_name,$command,$conn){
  $sql="UPDATE votes SET idea='$idea_name' WHERE idea='$command' ";
  $result=$conn->query($sql);
    if ($result==1) {
      return 'success';
    }else{
      return 'update failed';
    }
    $conn->close();
}
/*
*function for picking investment data
*/
function getIdeas($conn){
  $rows=array();
  $sql="SELECT * FROM investment";
  $result=$conn->query($sql);
  
    while($row=$result->fetch_assoc()){
      $rows[]=$row;
    }
    return $rows;
  
  $conn->close();
}
/*
*function for picking cash and loan data
*/
function picLoanAndCash($conn){
  $money=array();
  $sql1="SELECT SUM(amount) AS 'contributions' FROM contribution";
  $sql2="SELECT amount FROM savings";
  $sql3="SELECT SUM(balance) AS 'loans' FROM loan";
  $result1=$conn->query($sql1);
  $result2=$conn->query($sql2);
  $result3=$conn->query($sql3);
  
  if ($conn->affected_rows>0) {
    $row1=$result1->fetch_assoc();
    $row2=$result2->fetch_assoc();
    $row3=$result3->fetch_assoc();
    $cash1=$row1['contributions'];
    $cash2=$row2['amount'];
    $loans=$row3['loans'];
    $total_cash=$cash1+$cash2;
    $cash=$total_cash-$loans;
    $money[]=$cash;
    $money[]=$loans;
    return $money;
  }else {
    return "selection failed";
  }
  $conn->close();
}
/*
*function for calculating shares
*/
function getShares($conn){
  $sql="SELECT username FROM member";
  
  
  $result=$conn->query($sql);
  
  
  $user_contribution=array();
  while ($member=$result->fetch_assoc()) {
    $user=$member['username'];
    $shares=getPercent($user,$conn);
    $user_contribution[]=[$user=>$shares];
  }
  return $user_contribution;
  $conn->close();
}

function getPercent($user,$conn){
  $sql="SELECT SUM(amount) FROM contribution WHERE username='$user' ";
  $sql2="SELECT SUM(amount) FROM contribution";
  $result=$conn->query($sql);
  $result2=$conn->query($sql2);
  while ($rows=$result2->fetch_assoc()) {
    $total=$rows['SUM(amount)'];
    
  }
  while ($row=$result->fetch_assoc()) {
    $cont=$row['SUM(amount)'];
    $share=round(($cont/$total)*100,3);
    return $share;
  }
  
}
#calculating benefits
function getBenefits($conn){
  $final_array=array();
  $members=array();
  $sql2="SELECT SUM(amount) FROM savings";
  $result2=$conn->query($sql2);
  while ($a=$result2->fetch_assoc()) {
    $sacco=$a['SUM(amount)'];
  }
  $sql="SELECT username FROM member";
  $result=$conn->query($sql);
  while($i=$result->fetch_assoc()){
     $members[]=$i['username'];
  }
  foreach ($members as $member) {
    $sql1="SELECT SUM(amount) FROM benefits WHERE username='$member'";
    $result1=$conn->query($sql1);
    while ($a = $result1->fetch_assoc()){
     $amount=$a['SUM(amount)'];
     $final_array[]=[$member=>$amount];
    }
  }
  $final_array[]=['sacco savings'=>$sacco];

  // $sql="SELECT SUM(profits) FROM investment";
  // $result=$conn->query($sql);
  // while($row=$result->fetch_assoc()){
  //   $profits=$row['SUM(profits)'];
  // }
  // $sacco_saving=round(0.3*$profits);
  // $final_array[]=['sacco_savings'=>$sacco_saving];
  // $best_member_percentage=round(0.05*$profits);
  // $tobeshared=round(0.65*$profits);
  // #calculate the benefits of each member from the 60 percent
  // foreach ($member_shares as $share) {
  //   $values=$share;
  //   foreach ($values as $key => $value) {
  //     $value=round(($value/100)*$tobeshared);
  //     $newarray[]=[$key => $value];
  //   }
  // }
  
  // #get the member with the higheat value
  // $limit=0;
  // foreach ($newarray as $array) {
  //   $first_step=$array;
  //   $max=max($first_step);
  //   if($max>$limit){
  //     $limit=$max;
  //   }
  // }
  // #adding the 5 percent to the member with the highest value
  // foreach ($newarray as $myarray) {
  //   $second_step=$myarray;
  //   foreach ($second_step as $key => $value) {
  //     if ($value==$limit) {
  //       $final_array[]=[$key => ($value+$best_member_percentage)];
  //     }else{
  //       $final_array[]=[$key => $value];
  //     }
      
  //   }
  // }
  return $final_array;
  $conn->close();
 }
/*
 *Geting the legular members
 */
 function getRegularMember($conn){
     $members=array();
     $sql="SELECT username FROM member";
     $result=$conn->query($sql);
     while ($val=$result->fetch_assoc()) {
       $members[]=$val['username'];
     }
     
     $secondtoday=date("Y-m-d", strtotime("-1 months"));
     $thirdtoday=date("Y-m-d", strtotime("-2 months"));
     $fourthtoday=date("Y-m-d", strtotime("-3 months"));
     $fifthtoday=date("Y-m-d", strtotime("-4 months"));
     $sixthtoday=date("Y-m-d", strtotime("-5 months"));
     $first_month=checkFirstMonth($conn,1,$members);
     $seccond_month=checkMonth($conn,2,$first_month,$secondtoday);
     $third_month=checkMonth($conn,3,$seccond_month,$thirdtoday);
     $fourth_month=checkMonth($conn,4,$third_month,$fourthtoday);
     $fifth_month=checkMonth($conn,5,$fourth_month,$fifthtoday);
     $sixth_month=checkMonth($conn,6,$fifth_month,$sixthtoday);
     return $sixth_month;
 }
 //checkin for regularity of the members
 function checkFirstMonth($conn,$months,$members){
     $remaining=array();
     $today=DATE('Y-m-d');
     
     $target = date("Y-m-d", strtotime("-{$months} months"));
     $sql="SELECT username FROM contribution WHERE date<='$today' AND date>='$target' ";
    $result=$conn->query($sql);
    while ($a = $result->fetch_assoc()) {
      foreach ($members as $key => $value) {
        if ($value==$a['username']) {
          $remaining[]=$a['username'];
        }
      }
    }
    return $remaining;
    $conn->close();
 }
function checkMonth($conn,$months,$members,$today){
     $remaining=array();
     
     $target = date("Y-m-d", strtotime("-{$months} months"));
     
    $sql="SELECT username FROM contribution WHERE date<='$today' AND date>='$target' ";
    $result=$conn->query($sql);
    while ($a = $result->fetch_assoc()) {
      for($i=0;$i<sizeof($members);$i++) {
        if ($members[$i]==$a['username']) {
          $remaining[]=$a['username'];
        }
      }
    }
    return $remaining;
    $conn->close();
}

/*
 *geting the total contribution of a user
 */
function getCont($user,$conn){
  $sql="SELECT SUM(amount) FROM contribution WHERE username='$user' ";
  $result=$conn->query($sql);
  while ($rows=$result->fetch_assoc()) {
    $amount=$rows['SUM(amount)'];
  }
  return $amount;
  $conn->close();
}
/*
 *get the member with highest contribution
 */
function getHighest($conn){
  $users=array();
  $amounts=array();
   $sql1="SELECT username FROM  member";
   
   $result1=$conn->query($sql1);
   while ($a = $result1->fetch_assoc()) {
     $users[]=$a['username'];
   }
   for ($i=0; $i < sizeof($users); $i++) { 
    $sql2="SELECT SUM(amount) FROM contribution WHERE username='$users[$i]'";
    $result2=$conn->query($sql2);
    while($val=$result2->fetch_assoc()){
    $amounts[]=$val['SUM(amount)'];
    }
   }
   $largest=max($amounts);
   $three_quarter=(3/4)*$largest;
   return $three_quarter;
   $conn->close();
}
/*
 *loan details
 */
function getLoans($conn){
   $sql="SELECT * FROM loan";
   $loans=array();
   $result=$conn->query($sql);
   while ($a = $result->fetch_assoc()) {
    $loans[]=$a;
   }
   return $loans;
}
#pay benefits
function payBenefits($conn,$profits,$idea){
  $first_array=array();
  $date=DATE('Y-m-d');
  $shares=getShares($conn);
  $sacco_amount=round(0.3*$profits,3);
  $sql1="INSERT INTO savings(id, date, amount, idea) VALUES(NULL, '$date', '$sacco_amount', '$idea')";
  $result=$conn->query($sql1);
  $highest=round(0.05*$profits,3);
  $shared=round(0.65*$profits,3);
  $limit=0;
  foreach ($shares as $array) {
   foreach ($array as $key => $value) {
      if ($value>$limit) {
        $limit=$value;
      }
   }
  }
  foreach ($shares as $share) {
    foreach ($share as $key => $value) {
      if ($value==$limit) {
        $new_value=round((($value/100)*$shared)+$highest,3);
        $first_array[]=[$key=>$new_value];
      }else{
      $new_value=round(($value/100)*$shared,3);
      $first_array[]=[$key=>$new_value];
      }
    }
  }


  foreach ($first_array as $val) {
    foreach ($val as $key => $value) {
      
      $sql="INSERT INTO benefits(id, date, amount, idea, username) VALUES(NULL, '$date', '$value', '$idea', '$key')";
      $conn->query($sql);
      
    }
  }
  $conn->close();
}

#get benefits
function getBenefitsDetails($conn){
  $sql="SELECT * FROM benefits";
  $result=$conn->query($sql);
  $benefits=array();
  while($a=$result->fetch_assoc()){
    $benefits[]=$a;
  }
  return $benefits;
}

#check if loan exist
function checkLoan($username,$conn){
   $sql="SELECT * FROM loan WHERE username='$username' AND status='running'";
  $result=$conn->query($sql);
  if ($conn->affected_rows!=0) {
    return "user has a loan";
  }else {
    return "user has no loan";
  }
  $conn->close();
}

#pay loan
function payLoan($date_of_payement,$amount,$username,$conn){

  $sql1="SELECT balance FROM loan WHERE username='$username' AND status='running'";
  $result1=$conn->query($sql1);
  while ($a = $result1->fetch_assoc()) {
   $balance=$a['balance'];
  //  print_r($a);
  }
  if ($balance!=NULL) {
    $new_balance=$balance-$amount;
  $sql2="UPDATE loan SET balance='$new_balance' WHERE username='$username' AND status='running'";
  $result2=$conn->query($sql2);
  if($result2==1){
    $sql3="INSERT INTO loan_transactions(id, date_of_payement, amount, username) VALUES(NULL, '$date_of_payement', '$amount', '$username')";
  $result3=$conn->query($sql3);
  return $result3;
  }else{
    return $result2;
  }
  }else{
    return 'failed';
  }
  
  $conn->close();
}

#monitor loans
function monitorLoans($conn){
  $sql="SELECT username, balance FROM loan";
  $result=$conn->query($sql);
  while ($a = $result->fetch_assoc()) {
   if ($a['balance']==0) {
     $username=$a['username'];
     
     $result1=$conn->query("UPDATE loan SET status='cleared' WHERE username='$username' AND balance='0'");
     
   }
  }
}
  function indexpage(){
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Family sacco</title>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="css/freelancer.css" rel="stylesheet">
    <!-- <link href="css/style.css" rel="stylesheet"> -->

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="page-top" class="index">
<div id="skipnav"><a href="#maincontent">Skip to main content</a></div>

    <!-- Navigation -->
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top navbar-custom">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#page-top">Sacco</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>

                    <!-- <li class="page-scroll">
                        <a href="#contact">Login</a>
                    </li> -->
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

   


    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <div class="text-center">
                <h1>Welcome To Our Family Sacco</h1>
                <hr class="star-primary">
            </div>
            
             <?php
                if (isset($_GET['message'])) {
                    echo $_GET['message'];
                }
            ?>
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h3>Member login</h3>
                    
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <!-- To configure the contact form email address, go to mail/contact_me.php and update the email address in the PHP file on line 19. -->
                    <!-- The form should work on most web servers, but if the form is not working you may need to configure your web server differently. -->
                    <form name="sentMessage" id="contactForm" action="index.php" method="post">
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label for="name">Username</label>
                                <input type="text" class="form-control" placeholder="Username" id="username" name="username" required data-validation-required-message="Please enter your Username.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label for="password">Password</label>
                                <div class="input-group">
                                <input type="password" class="form-control" placeholder="Password" id="pass" name="password" required data-validation-required-message="Please enter your password.">
                                <span class="input-group-addon" id="passcontrol" data-toggle="tooltip" ><span class="glyphicon glyphicon-eye-open"></span></span>
                                </div>
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>

                                                <br>
                        <div id="success"></div>
                        <div class="row">
                            <div class="form-group col-xs-12">
                                <button type="submit" name="submit" class="btn btn-success btn-lg">Login</button>
                            </div>
                        </div>
                    </form>
                    <div class="text-center">
                         <a class="btn btn-success" role="button" data-toggle="collapse" href="#new_idea_form" aria-expanded="false" aria-controls="new_idea_form">
                        For examiners use only
                        </a>
                        <div class="collapse" id="new_idea_form">
                        <div class="alert alert-info" style="font-size:2em;">
                            Use <i style="color:red;">admin</i> as username and <i style="color:red;">12345</i> as password to login as an admin
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center">

        <div class="footer-below">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        Copyright &copy; Group one recess 2017
                    </div>
                </div>
            </div>
        </div>
    </footer>



    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

    <!-- Theme JavaScript -->
    <script src="js/freelancer.min.js"></script>
    <script>
        // let val=document.getElementById('index-message').innerHTML;
        // if(val=='invalid username or password'){
        //     document.getElementById('index-message').addClass('alert alert-danger');
        // }else{
        //     document.getElementById('index-message').addClass('alert alert-danger');
        // }
       $(function(){
           $('#passcontrol').mousedown(function(){
               $("#pass").prop("type", "text");
           });
            $('#passcontrol').mouseup(function(){
               $("#pass").prop("type", "password");
           });
           
           
       });
      </script>
</body>

</html>


   <?php 
}
function admin(){
   ?>


<?php
  // session_start();
  if($_SESSION['position']!='admin'){
      header('Location:index.php');
  }
  $requests=$_SESSION['pending'];
  $idea_detailes=$_SESSION['idea'];
  $loan_cash=$_SESSION['cash_loan'];
  $benefits=$_SESSION['benefits'];
  
?>

<!DOCTYPE html>
   <html lang="en">
    <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="group one recess">

    <title>Family sacco| admin</title>
    <!-- main script  -->
    <script type="text/javascript" src="js/index.js"></script>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="css/freelancer.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
</head>
<body id="page-top" class="index" onload="updateFiles();">
<div id="skipnav"><a href="#maincontent">Skip to main content</a></div>
      <!-- Navigation -->
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top navbar-custom">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#page-top">Sacco</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <!-- <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Reports</a></li>
                    <li><a href="#">Add Member</a></li>
                    <li>
                        <a href="#">Logout</a>
                    </li>
                </ul> -->
                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li class="page-scroll">
                        <a href="index.php?admin">Home</a>
                    </li>
                    <li>
                        <a href="index.php?adminreports">Reports</a>
                    </li>
                    <li class="page-scroll">
                        <a href="#addmember">Add Member</a>
                    </li>
                    <li>
                        <form method="post" action="index.php">
                         <input type="submit" name="logout" value="Logout" class="btn btn-success btn-lg">
                        </form>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <div class="container" id="maincontent" tabindex="-1">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header">
          <h1>Welcome To Family Sacco</h1>
          <div class="" id="index-message" style="font-size:2em; color:green; background-color:white;">
                      <?php
                        if (isset($_GET['message'])) {
                            echo $_GET['message'];
                        }
                      ?>
                    </div>
                    <div class="" id="index-error" style="font-size:2em; color:red; background-color:white;">
                      <?php
                        if (isset($_GET['error'])) {
                            echo $_GET['error'];
                        }
                      ?>
                    </div>
        </div>
        <!-- pending requests accordion -->
        <div class="panel-group" id="pending1_accordion">
          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#requests" data-toggle="collapse" data-parent="#pending1_accordion">Pending requests </a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="requests">
              <div class="panel-body">
                <div class="">
                  <table class="table table-striped table-bordered table-hover table-condensed table-responsive">
                    <?php
                      foreach($requests as $request){
                          $words=explode(" ",strtolower($request));
                          if ($words[0]=='contribution'||$words[0]=='loan') {
                            echo "<form method='post' action='index.php'>";
                            echo "<tr>";
                            echo "<td>";
                            echo "<input type='text' name='handel_request' value='{$request}' class='form-control'>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-info' name='approve_contribution'>Approve</button>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-danger' name='deny_contribution'>Deny</button>";
                            echo "</td>";
                            echo "<tr>";
                            echo "</form>";
                          }
                      }
                    ?>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

       <!--pending loan payments  -->

       <div class="panel-group" id="pending1_accordion">
          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#loans" data-toggle="collapse" data-parent="#pending1_accordion">Pending Loan Payments</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="loans">
              <div class="panel-body">
                <div class="">
                  <table class="table table-striped table-bordered table-hover table-condensed table-responsive">
                    <?php
                      foreach($requests as $request){
                          $words=explode(" ",strtolower($request));
                          if ($words[0]=='payloan') {
                            echo "<form method='post' action='index.php'>";
                            echo "<tr>";
                            echo "<td>";
                            echo "<input type='text' name='handel_request' value='{$request}' class='form-control'>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-info' name='approve_contribution'>Approve</button>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-danger' name='deny_contribution'>Deny</button>";
                            echo "</td>";
                            echo "<tr>";
                            echo "</form>";
                          }
                      }
                    ?>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

       <!-- pending business ideas accordion -->
        <div class="panel-group" id="pending2_accordion">
          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#ideas" data-toggle="collapse" data-parent="#pending2_accordion">Pending business ideas </a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="ideas">
              <div class="panel-body">
                <div class="">
                  <table class="table table-striped table-bordered table-hover table-condensed table-responsive">
                    <?php
                     $id=1;
                      foreach($requests as $request){
                          $words=explode(" ",strtolower($request));
                          if ($words[0]=='idea') {
                            echo "<form method='post' action='index.php'>";
                            echo "<tr>";
                            echo "<td>";
                            echo "<input type='text' name='handel_idea' value='{$request}' id='{$id}' class='form-control'>";
                            echo "</td>";
                            echo "<td>";
                            echo "<a class='btn btn-info' onmouseover='voteCount({$id},\"likes\")'><i class='fa fa-thumbs-up' aria-hidden='true'></i>Likes<span id='badgel{$id}' class='badge badge-info'></span></a>";
                            echo "<a class='btn btn-danger' onmouseover='voteCount({$id},\"dislikes\")'><i class='fa fa-thumbs-down' aria-hidden='true'></i>Dislikes<span class='badge badge-info' id='badged{$id}'></span></a>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-info' name='approve_idea'>Approve</button>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-danger' name='deny_idea'>Deny</button>";
                            echo "</td>";
                            echo "<tr>";
                            echo "</form>";
                            $id++;
                          }
                      }
                    ?>
                  </table>
                  <div class="alert alert-info"><h4>Hover over the <a class="btn btn-info"><i class="fa fa-thumbs-up" aria-hidden="true"></i>Likes</a> and <a class="btn btn-danger"><i class="fa fa-thumbs-up" aria-hidden="true"></i>Dislikes</a> to see the how many they are<br><small>Note: Approve an idea after entering its details.</small></h4></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- accordion for entering business idea details-->
         <div class="panel-group" id="idea_accordion">
           <div class="panel panel-customise">
             <div class="panel-heading">
               <h4 class="panel-title">
                 <span class="glyphicon glyphicon-menu-down"></span>
                 <a href="#ideas_details" data-toggle="collapse" data-parent="#idea_accordion">Enter business idea details </a>
               </h4>
             </div>
             <div class="panel-collapse collapse" id="ideas_details">
               <div class="panel-body">
                 <div class="">
                   <a class="btn btn-info" role="button" data-toggle="collapse" href="#new_idea_form" aria-expanded="false" aria-controls="new_idea_form">
                    New business idea
                    </a>
                    <button class="btn btn-success" type="button" data-toggle="collapse" data-target="#existing_idea_form1" aria-expanded="false" aria-controls="existing_idea_form1">
                    Profits
                    </button>
                    <button class="btn btn-danger" type="button" data-toggle="collapse" data-target="#existing_idea_form2" aria-expanded="false" aria-controls="existing_idea_form2">
                    Losses
                    </button>
                    <div class="collapse" id="new_idea_form">
                    <div class="well">
                        <form method="post" action="index.php">
                          <div class="form-group">
                            <label>Idea Name</label>
                            <input type="text" name="idea" class="form-control" placeholder="Idea Name" required onchange="checkIdea(this.value,'new');">
                          </div>
                          <div id="new_idea_messenger" class=""></div>
                          <div class="form-group">
                            <label>initial_amount</label>
                            <input type="number" name="initial_amount" class="form-control" placeholder="Initial_amount" required>
                          </div>
                          <div class="form-group">
                            <label>Suggested by</label>
                            <input type="text" name="username" class="form-control" placeholder="Suggested by" required>
                          </div>
                          <input type="submit" class="btn btn-info" value="submit" name="submit_new_idea">
                        </form>
                    </div>
                    </div>
                    <div class="collapse" id="existing_idea_form1">
                    <div class="well">
                       <form method="post" action="index.php">
                          <div class="form-group">
                            <label>Idea Name</label>
                            <input type="text" name="idea" id="idea" class="form-control" placeholder="Idea Name" required onchange="checkIdea(this.value,'profits');">
                          </div>
                          <div id="idea_messenger1" class=""></div>
                          <div class="form-group">
                            <label>Profits</label>
                            <input type="number" name="profit" class="form-control" placeholder="Profits" required>
                          </div>
                         
                          <input type="submit" class="btn btn-success" value="submit" name="profits">
                        </form>
                    </div>
                    </div>
                    <div class="collapse" id="existing_idea_form2">
                    <div class="well">
                       <form method="post" action="index.php">
                          <div class="form-group">
                            <label>Idea Name</label>
                            <input type="text" name="idea" id="idea" class="form-control" placeholder="Idea Name" required onchange="checkIdea(this.value,'losses');">
                          </div>
                          <div id="idea_messenger2" class=""></div>
                          
                          <div class="form-group">
                            <label>Losses</label>
                            <input type="number" name="loss" class="form-control" placeholder="Losses" required>
                          </div>
                          <input type="submit" class="btn btn-danger" value="submit" name="losses">
                        </form>
                    </div>
                    </div>
                 </div>
               </div>
             </div>
           </div>
         </div>
                </div>
            </div>
        </div>

      <!-- Add member -->
    <section id="addmember" class="col-lg-10 col-lg-offset-1">
      <h1 style="color: black;">Add New Member</h1>
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h3>Member Details</h3>
                    <hr class="star-primary">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <!-- To configure the contact form email address, go to mail/contact_me.php and update the email address in the PHP file on line 19. -->
                    <!-- The form should work on most web servers, but if the form is not working you may need to configure your web server differently. -->
                    <form name="sentMessage" id="contactForm" method="post" action="index.php">
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label for="fname">First Name</label>
                                <input type="text" class="form-control" placeholder="First Name" id="fname" name="fname" required >

                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label for="lname">Last Name</label>
                                <input type="text" class="form-control" placeholder="Last Name" id="fname" name="lname" required >

                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label for="Username">Username</label>
                                <input type="text" class="form-control" placeholder="Username" id="username" name="username" required>

                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" placeholder="Password" id="password" name="password" required>

                            </div>
                        </div>
                        <br>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label for="initial_deposit">Initial Deposit</label>
                                <input type="text" class="form-control" placeholder="Initial Deposit" id="initial_deposit" name="initial_deposit" required>

                            </div>
                        </div>
                        <br>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 ">
                                <label for="position">Position</label>
                                <select name="position" id="position" class="form-control">
                                  <option value="member">Member</option>
                                <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div id="success"></div>
                        <div class="row">
                            <div class="form-group col-xs-12">
                                <button type="submit" name="submit_member" class="btn btn-success btn-block">Send</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>


      <!-- Footer -->
    <footer class="text-center navbar-fixed-bottom">
        <div class="footer-below">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        Copyright &copy; Group one recess 2017
                    </div>
                </div>
            </div>
        </div>
    </footer>
      <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

    

    <!-- Theme JavaScript -->
    <script src="js/freelancer.min.js"></script>
      <script>
        $(function(){
          $(".panel-collapse").on("hidden.bs.collapse",function(){
              $(this).parent().find(".glyphicon").removeClass("glyphicon-menu-up").addClass("glyphicon-menu-down");
           });
          $(".panel-collapse").on("shown.bs.collapse",function(){
              $(this).parent().find(".glyphicon").removeClass("glyphicon-menu-down").addClass("glyphicon-menu-up");
          });
          $("#btnpop").popover();
          $("#btnpop2").popover();
        });
      </script>

    </body>
</html>  


  

   <?php 
}
function adminreports(){
   ?>

   <?php
  // session_start();
  if($_SESSION['position']!='admin'){
      header('Location:index.php');
  }
  $idea_detailes=$_SESSION['idea'];
  $loan_cash=$_SESSION['cash_loan'];
  $benefits=$_SESSION['benefits'];
   $regular_member=$_SESSION['reguler_member'];
    $loans=$_SESSION['loans'];
    $shares=$_SESSION['shares'];
    $benefits_details=$_SESSION['benefits_details'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Family sacco| admin</title>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="css/freelancer.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
    <body id="page-top" class="index">
<div id="skipnav"><a href="#maincontent">Skip to main content</a></div>
      <!-- Navigation -->
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top navbar-custom">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#page-top">Sacco</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li class="page-scroll">
                        <a href="index.php?admin">Home</a>
                    </li>
                    <li>
                        <a href="#page-top">Reports</a>
                    </li>
                    <li class="page-scroll">
                        <a href="index.php?admin#addmember">Add Member</a>
                    </li>
                    <li>
                        <form method="post" action="index.php">
                         <input type="submit" name="logout" value="Logout" class="btn btn-success btn-lg">
                        </form>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <div class="container" id="maincontent" tabindex="-1">

   <div class="row">
                <div class="col-lg-12">
                  <div class="header">
                    <h1>Family sacco reports</h1>
                  </div>
        <!-- Reports accordion -->
        <div class="panel-group" id="reports_accordion">
          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report1" data-toggle="collapse" data-parent="#reports_accordion">How much is in loans and cash</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report1">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Cash(UGX)</th>
                     <th>Loans(UGX)</th>
                     </tr>
                     </thead>
                     <?php 
                       echo "<td>".$loan_cash[0]."</td>";
                       echo "<td>".$loan_cash[1]."</td>";
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report2" data-toggle="collapse" data-parent="#reports_accordion">List of regular members</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report2">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Number</th>
                     <th>Member Username</th>
                     </tr>
                     </thead>
                     <?php 
                      
                      $num=1;
                      foreach ($regular_member as $value) {
                        echo "<tr>";
                        echo "<td>".$num."</td>";
                        echo "<td>".$value."</td>";
                        echo "</tr>";
                        $num++;
                      }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report3" data-toggle="collapse" data-parent="#reports_accordion">Details of the business ideas</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report3">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Idea Name</th>
                     <th>Initial Investment</th>
                     <th>Approved On</th>
                     <th>Profits</th>
                     <th>Losses</th>
                     <th>Suggested by</th>
                     </tr>
                     </thead>
                     <?php 
                       foreach($idea_detailes as $detail){
                         echo "<tr>";
                         echo "<td>".$detail['idea']."</td>";
                         echo "<td>".$detail['initial_amount']."</td>";
                         echo "<td>".$detail['date_of_approval']."</td>";
                         echo "<td>".$detail['profits']."</td>";
                         echo "<td>".$detail['losses']."</td>";
                         echo "<td>".$detail['username']."</td>";
                         echo "</tr>";
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report4" data-toggle="collapse" data-parent="#reports_accordion">Amounts in benefits</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report4">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Member Username</th>
                     <th>Benefits Amount(UGX)</th>
                     </tr>
                     </thead>
                     <?php 
                       foreach($benefits as $benefit){
                         foreach ($benefit as $key => $value) {
                            echo "<tr>";
                            echo "<td>".$key."</td>";
                            echo "<td>".$value."</td>";
                            echo "</tr>";
                         }
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report9" data-toggle="collapse" data-parent="#reports_accordion">Benefits Details</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report9">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Username</th>
                     <th>Benefits Amount(UGX)</th>
                     <th>Business Idea</th>
                     <th>Date</th>
                     </tr>
                     </thead>
                     <?php 
                       foreach($benefits_details as $detail){
                         echo "<tr>";
                         echo "<td>".$detail['username']."</td>";
                         echo "<td>".$detail['amount']."</td>";
                         echo "<td>".$detail['idea']."</td>";
                         echo "<td>".$detail['date']."</td>";
                         echo "</tr>";
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report5" data-toggle="collapse" data-parent="#reports_accordion">Loan Details</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report5">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Username</th>
                     <th>Amount</th>
                     <th>Date_of_issue</th>
                     <th>Balance</th>
                     
                     </tr>
                     </thead>
                     <?php 
                       foreach($loans as $loan){
                         echo "<tr>";
                         echo "<td>".$loan['username']."</td>";
                         echo "<td>".$loan['amount']."</td>";
                         echo "<td>".$loan['date_of_issue']."</td>";
                         echo "<td>".$loan['balance']."</td>";
                         echo "</tr>";
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report6" data-toggle="collapse" data-parent="#reports_accordion">Member shares</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report6">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Username</th>
                     <th>Shares(%)</th>
                     
                     
                     </tr>
                     </thead>
                     <?php 
                       foreach ($shares as $share) {
                        foreach ($share as $key => $value) {
                          echo "<tr>";
                          echo "<td>".$key."</td>";
                          echo "<td>".$value."</td>";
                          echo "</tr>";
                        }
                      
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>


        </div>

        


</div>
      
       
      <!-- Footer -->
    <footer class="text-center navbar-fixed-bottom">
        <div class="footer-below">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        Copyright &copy; Group one recess 2017
                    </div>
                </div>
            </div>
        </div>
    </footer>
      <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

    <!-- Theme JavaScript -->
    <script src="js/freelancer.min.js"></script>
      <script>
        $(function(){
          $(".panel-collapse").on("hidden.bs.collapse",function(){
              $(this).parent().find(".glyphicon").removeClass("glyphicon-menu-up").addClass("glyphicon-menu-down");
           });
          $(".panel-collapse").on("shown.bs.collapse",function(){
              $(this).parent().find(".glyphicon").removeClass("glyphicon-menu-down").addClass("glyphicon-menu-up");
          });
        });
      </script>

    </body>
</html>



   <?php
}
function reports(){
?>

<?php
  // session_start();
  if($_SESSION['position']!='member'){
      header('Location:index.php');
  }
  $requests=$_SESSION['pending'];
  $username=$_SESSION['username'];
  $idea_detailes=$_SESSION['idea'];
  $loan_cash=$_SESSION['cash_loan'];
  $benefits=$_SESSION['benefits'];
  $regular_member=$_SESSION['reguler_member'];
  $loans=$_SESSION['loans'];
 
?>
<!DOCTYPE html>
<html lang="en">
    <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Family sacco| reports</title>

    <!-- main script  -->
    <script type="text/javascript" src="js/index.js"></script>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
    
    <!-- Theme CSS -->
    <link href="css/freelancer.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body id="page-top" class="index">
<div id="skipnav"><a href="#maincontent">Skip to main content</a></div>
        <!-- Navigation -->
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top navbar-custom">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#page-top">Sacco</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    
                    <li>
                        <form method="post" action="index.php">
                         <input type="submit" name="logout" value="Logout" class="btn btn-success btn-lg">
                        </form>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>
     
     <div class="container" id="maincontent" tabindex="-1">
            <div class="row">
                <div class="col-lg-12">
                  <div class="header">
                    <h1>Family sacco reports</h1>
                  </div>
        <!-- Reports accordion -->
        <div class="panel-group" id="reports_accordion">
          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report1" data-toggle="collapse" data-parent="#reports_accordion">How much is in loans and cash</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report1">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Cash(UGX)</th>
                     <th>Loans(UGX)</th>
                     </tr>
                     </thead>
                     <?php 
                       echo "<td>".$loan_cash[0]."</td>";
                       echo "<td>".$loan_cash[1]."</td>";
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report2" data-toggle="collapse" data-parent="#reports_accordion">List of regular members</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report2">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Number</th>
                     <th>Member Username</th>
                     </tr>
                     </thead>
                     <?php 
                      
                      $num=1;
                      foreach ($regular_member as $value) {
                        echo "<tr>";
                        echo "<td>".$num."</td>";
                        echo "<td>".$value."</td>";
                        echo "</tr>";
                        $num++;
                      }
                    
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report3" data-toggle="collapse" data-parent="#reports_accordion">Details of the business ideas</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report3">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Idea Name</th>
                     <th>Initial Investment(UGX)</th>
                     <th>Approved On</th>
                     <th>Profits(UGX)</th>
                     <th>Losses(UGX)</th>
                     <th>Suggested by</th>
                     </tr>
                     </thead>
                     <?php 
                       foreach($idea_detailes as $detail){
                         echo "<tr>";
                         echo "<td>".$detail['idea']."</td>";
                         echo "<td>".$detail['initial_amount']."</td>";
                         echo "<td>".$detail['date_of_approval']."</td>";
                         echo "<td>".$detail['profits']."</td>";
                         echo "<td>".$detail['losses']."</td>";
                         echo "<td>".$detail['username']."</td>";
                         echo "</tr>";
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report4" data-toggle="collapse" data-parent="#reports_accordion">Amounts in benefits</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report4">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Member Username</th>
                     <th>Benefits Amount(UGX)</th>
                     </tr>
                     </thead>
                     <?php 
                       foreach($benefits as $benefit){
                         foreach ($benefit as $key => $value) {
                            echo "<tr>";
                            echo "<td>".$key."</td>";
                            echo "<td>".$value."</td>";
                            echo "</tr>";
                         }
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>
          
          <div class="panel panel-customise">
            <div class="panel-heading">
              <h4 class="panel-title">
                <span class="glyphicon glyphicon-menu-down"></span>
                <a href="#report5" data-toggle="collapse" data-parent="#reports_accordion">Loan Details</a>
              </h4>
            </div>
            <div class="panel-collapse collapse" id="report5">
              <div class="panel-body">
                <div class="">
                  <table class="table table-responsive table-striped table-hover table-compact table-bordered">
                    <thead>
                    <tr class="" style="background-color:#18bc9c; color:white;">
                     <th>Username</th>
                     <th>Amount</th>
                     <th>Date_of_issue</th>
                     <th>Balance</th>
                     
                     </tr>
                     </thead>
                     <?php 
                       foreach($loans as $loan){
                         echo "<tr>";
                         echo "<td>".$loan['username']."</td>";
                         echo "<td>".$loan['amount']."</td>";
                         echo "<td>".$loan['date_of_issue']."</td>";
                         echo "<td>".$loan['balance']."</td>";
                         echo "</tr>";
                       }
                     ?>
                    
                  </table>
                </div>
              </div>
            </div>
          </div>
          
        </div>

        <!-- pending business ideas accordion -->
         <div class="panel-group" id="pending2_accordion">
           <div class="panel panel-customise">
             <div class="panel-heading">
               <h4 class="panel-title">
                 <span class="glyphicon glyphicon-menu-down"></span>
                 <a href="#ideas" data-toggle="collapse" data-parent="#pending2_accordion">vote for suggested business ideas </a>
               </h4>
             </div>
             <div class="panel-collapse collapse" id="ideas">
               <div class="panel-body">
                 <div class="">
                   <table class="table table-striped table-bordered table-hover table-condensed table-responsive">
                    <?php
                     $name=$username;
                     
                     $id=1;
                      foreach($requests as $request){
                          $words=explode(" ",strtolower($request));
                          if ($words[0]=='idea') {
                            echo "<div id='approve_messemger' class=''></div>";
                            echo "<div id='reject_messemger' class=''></div>";
                            echo "<tr>";
                            echo "<td>";
                            echo "<input type='text' id='{$id}' name='handel_idea' value='{$request}' class='form-control'>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-info' name='approve_idea' value='approve' onclick='Votes(this.value,{$id},\"{$name}\");'><i class='fa fa-thumbs-up' aria-hidden='true'></i>Approve</button>";
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-danger' name='deny_idea' value='reject' onclick='Votes(this.value,{$id},\"{$name}\");'><i class='fa fa-thumbs-down' aria-hidden='true'></i>Reject</button>";
                            echo "</td>";
                            echo "<tr>";
                            $id=$id+1;
                          }
                          
                      }
                    ?>
                  </table>
                 </div>
               </div>
             </div>
           </div>
         </div>
        </div>
      </div>
     </div>

      <!-- Footer -->
    <footer class="text-center navbar-fixed-bottom">
                            
        <div class="footer-below">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        Copyright &copy; Group one recess 2017
                    </div>
                </div>
            </div>
        </div>
    </footer>
      <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

    <!-- Theme JavaScript -->
    <script src="js/freelancer.min.js"></script>
      <script>
        $(function(){
          $(".panel-collapse").on("hidden.bs.collapse",function(){
              $(this).parent().find(".glyphicon").removeClass("glyphicon-menu-up").addClass("glyphicon-menu-down");
           });
          $(".panel-collapse").on("shown.bs.collapse",function(){
              $(this).parent().find(".glyphicon").removeClass("glyphicon-menu-down").addClass("glyphicon-menu-up");
          });
        });
      </script>
    </body>
</html>


<?php
}
?>