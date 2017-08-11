<?php

session_start();
 require('def.php');
 /*
 *update the files session responding to an ajax call
 */
 if (isset($_GET['value'])) {
   if ($_GET['value']=='update files') {
    //  echo $_GET['value'];
    $_SESSION['pending']=fileReader();
   }
 }
 
 #db information
 $dbhost='localhost';
 $dbuser='root';
 $dbpassword='';
 $db='sacco';
 #create db connection
 $conn=new mysqli($dbhost,$dbuser,$dbpassword,$db);
 if ($conn->connect_errno) {
   echo "database connection failed".$conn->connect_error;
 }else{
   /*
    *handeling system constraints
    */
   $files=fileReader();
   $loans=array();
   $bideas=array();
   for ($i=0; $i <sizeof($files); $i++) { 
    $words=explode(" ",strtolower($files[$i]));
    if ($words[0]=='loan') {
     $loans[]=trim($files[$i]);
    }elseif ($words[0]=='idea') {
     $bideas[]=trim($files[$i]);
    }
   }
   #monitor loans
   monitorLoans($conn);
   //investment  idea constraint
   ideas($bideas,$conn);
   //loan constraints
   $regulars=getRegularMember($conn);
   for ($i=0; $i <sizeof($loans); $i++) { 
     $newwords=explode(" ",$loans[$i]);
     $user=$newwords[4];
     $value=$newwords[2];
     if (in_array($user,$regulars)) {
       $total_cont=getCont($user,$conn);
       $tester=($total_cont/2);
       if ($value>$tester) {
         cutCommand($loans[$i]);
       }
     }else{
       cutCommand($loans[$i]);
     }
   }
   
   /*
    *loan details
    */
    $loan_details=getLoans($conn);
    $_SESSION['loans']=$loan_details;
   /*
     *regular members
    */
     $regular_members=getRegularMember($conn);
     $regular=array_unique($regular_members);
      
     $_SESSION['reguler_member']=$regular;
    
    /*
     *benefits
    */
    #get member shares
    $member_shares=getShares($conn);
    $_SESSION['shares']=$member_shares;
    $benefits=getBenefits($conn);
    $benefits_details=getBenefitsDetails($conn);
    $_SESSION['benefits_details']=$benefits_details;
    $_SESSION['benefits']=$benefits;
    #pick business idea details
    $investment_deatails=getIdeas($conn);
    if ($investment_deatails!='selection failed') {
      $_SESSION['idea']=$investment_deatails;
    }
    #pick loans and cash details
    $loancash=picLoanAndCash($conn);
    if ($loancash!='selection failed') {
      $_SESSION['cash_loan']=$loancash;
    }
   if(isset($_POST['submit'])){
   #collecting the username and password
   $username=$_POST['username'];
   $password=$_POST['password'];
   #calling the function to authenticate the login
   $permited=login($username,$password,$conn);
  // print_r($permited);
   if ($permited!='login failed') {
    // foreach ($permited as $key => $value) {
    //   echo $permited[$key];
    // }
    if($permited['position']=='admin'){
      $_SESSION['username']=$permited['username'];
      $_SESSION['position']=$permited['position'];
      //set session to hold the file content
      $_SESSION['pending']=fileReader();
      
      admin();
    }else{
      $_SESSION['username']=$permited['username'];
      $_SESSION['position']=$permited['position'];
      $_SESSION['pending']=fileReader();
      reports();
    }
  }else{
    // header('Location: ../index.php?message=invalid username or password');
    echo "<script>alert('invalid username or password')</script>";
    indexpage();
  }
 }

 #handeling new member registration
 elseif (isset($_POST['submit_member'])) {
   $fnamesmall=strtolower($_POST['fname']);
   $lnamesmall=strtolower($_POST['lname']);
   $fname=ucwords($fnamesmall);
   $lname=ucwords($lnamesmall);
   $username=$_POST['username'];
   $password=$_POST['password'];
   $position=$_POST['position'];
   $date_of_join=DATE('Y-m-d');
   if (is_numeric($_POST['initial_deposit'])) {
     $initial_deposit=$_POST['initial_deposit'];
   }else {
     // header('Location: ../admin.php?error=invalid initial deposit');
    echo "<script>alert('invalid initial deposit')</script>";
   	admin();
   }
   #check if username is already taken
   $taken=checkuser($username,$conn);
   $highest=getHighest($conn);
   if ($initial_deposit<$highest) {
      // header('Location: ../admin.php?error=new member initial deposit is too low');
     echo "<script>alert('new member initial deposit is too low')</script>";
   	admin();
   }else{
     handleContribution($initial_deposit,$date_of_join,$username,$conn);
      if ($taken=="not taken") {
        $registration=register($fname,$lname,$username,$password,$initial_deposit,$date_of_join,$position,$conn);
        if ($registration==1) {
          // header('Location: ../admin.php?message=new member registration successful');
          echo "<script>alert('new member registration successful')</script>";
        	admin();
        }else {
          // header('Location: ../admin.php?message=new member registration failed');
          echo "<script>alert('new member registration failed')</script>";
        	admin();
        }
      }else {
        // header('Location: ../admin.php?message=username already taken');
        echo "<script>alert('username already taken')</script>";
        
        admin();
      }
   }
  
 }

 #logging out
  elseif(isset($_POST['logout'])){
     session_destroy();
     indexpage();
   }
   #approve contribution
  elseif (isset($_POST['approve_contribution'])) {
    $command=$_POST['handel_request'];
    $command_vals=explode(" ",$command);
    if ($command_vals[0]=='contribution') {
      $amount=$command_vals[1];
      $date=$command_vals[2];
      $username=$command_vals[3];
      $timestamp=strtotime($date);
      $newdate=date('Y-m-d',$timestamp);  
      if($newdate!='1970-01-01'){
        $user_exists=verifyUser($username,$conn);
        if ($user_exists=='user exists') {
          $insert_contributon=handleContribution($amount,$newdate,$username,$conn);
          if ($insert_contributon=='success') {
            $cut_command=cutCommand(trim($command));
            // header('Location: ../admin.php?message=contribution approved successful');
            echo "<script>alert('contribution approved successful')</script>";
            admin();
          }else {
            // header('Location: ../admin.php?error=cotribution approval failed');
            echo "<script>alert('cotribution approval failed')</script>";
            admin();
          }
        }elseif ($user_exists=='user unknown') {
          // header('Location: ../admin.php?error=username unknown');
          echo "<script>alert('username unknown')</script>";
        	admin();
        }
      }else {
          // header('Location: ../admin.php?error=invalid date formart use(month/day/year)');
        echo "<script>alert('invalid date formart use(month/day/year)')</script>";
      	admin();
      }
    }elseif ($command_vals[0]=='loan') {
      $date_of_issue=DATE('Y-m-d');
      $amount=$command_vals[2];
      $three_percent=0.03*$amount;
      $username=$command_vals[4];
      $balance=$amount+$three_percent;
      $status='running';
      $user_exists=verifyUser($username,$conn);
        if ($user_exists=='user exists') {
          $check_loan=checkLoan($username,$conn);
          if ($check_loan=='user has no loan') {
                $insert_loan=handleLoan($amount,$date_of_issue,$balance,$status,$username,$conn);
              if ($insert_loan=='success') {
                $cut_command=cutCommand(trim($command));
                // header('Location: ../admin.php?message=loan approved successful');
                echo "<script>alert('loan approved successful')</script>";
                admin();
              }else {
                // header('Location: ../admin.php?error=loan approval failed');
                echo "<script>alert('loan approval failed')</script>";
                admin();
              }
          }else{
            // header('Location: ../admin.php?error=member has an uncleared loan');
            echo "<script>alert('member has an uncleared loan')</script>";
            admin();
          }
          
        }elseif ($user_exists=='user unknown') {
          // header('Location: ../admin.php?error=username unknown');
          echo "<script>alert('username unknown')</script>";
        	admin();
        }
    }elseif($command_vals[0]=='payloan'){
      $date_of_payement=DATE('Y-m-d');
      $amount=$command_vals[1];
      $username=$command_vals[2];
      
      $payloan=payLoan($date_of_payement,$amount,$username,$conn);
      if ($payloan==1) {
        $cut_command=cutCommand(trim($command));
        // header('Location: ../admin.php?message=loan payment approved successful');
        echo "<script>alert('loan payment approved successful')</script>";
        admin();
      }else{
        // header('Location: ../admin.php?error=loan payment failed');
        echo "<script>alert('loan payment failed')</script>";
        admin();
      }
    }
    
  }
  #deny contribution
  elseif (isset($_POST['deny_contribution'])) {
    $command=$_POST['handel_request'];
     $cut_command=cutCommand(trim($command));
     // header('Location: ../admin.php?message=contribution denied successful');
     echo "<script>alert('contribution denied successful')</script>";
     admin();
  }

  #approve idea
   elseif (isset($_POST['approve_idea'])) {
    $command=$_POST['handel_idea'];
    $command_data=explode(' ',$command);
    $idea_name=$command_data[1];
    $check_idea=checkForIdea($idea_name,$conn);
    if ($check_idea=='idea exists') {
      $update_vote=updateVote($idea_name,$command,$conn);
      if ($update_vote=='success') {
        $cut_command=cutCommand(trim($command));
        // header('Location: ../admin.php?message=idea approved successful');
         echo "<script>alert('idea approved successful')</script>";
        admin();
      }else{
        // header('Location: ../admin.php?message=idea approval failed');
        echo "<script>alert('idea approval failed')</script>";
        admin();
      }
    }else{
      // header('Location: ../admin.php?message=idea approval failed');
      echo "<script>alert('idea approval failed')</script>";
    	admin();
    }
  }
  #deny idea
  elseif (isset($_POST['deny_idea'])) {
    $command=trim($_POST['handel_idea']);
    $cut_command=cutCommand(trim($command));
     // header('Location: ../admin.php?message=idea denied successful');
    echo "<script>alert('idea denied successful')</script>";
    admin();
  }
  
  #insert a new business idea
 elseif (isset($_POST['submit_new_idea'])) {
    $idea=$_POST['idea'];
    $initial_amount=$_POST['initial_amount'];
    $date_of_approval=DATE('Y-m-d');
    $profits=0;
    $losses=0;
    $username=$_POST['username'];
    $user_exists=verifyUser($username,$conn);
        if ($user_exists=='user exists') {
          $insert_idea=handleIdea($idea,$initial_amount,$date_of_approval,$profits,$losses,$username,$conn);
          if ($insert_idea=='success') {
            // header('Location: ../admin.php?message=idea created successful');
            echo "<script>alert('idea created successful')</script>";
            admin();
          }else {
            // header('Location: ../admin.php?error=idea creation failed');
            echo "<script>alert('idea creation failed')</script>";
            admin();
          }
        }elseif ($user_exists=='user unknown') {
          // header('Location: ../admin.php?error=username unknown');
           echo "<script>alert('username unknown')</script>";
        	admin();
        }
    
  }

  #insert data for an already existin business idea
  elseif (isset($_POST['profits'])) {
    $idea=$_POST['idea'];
    $profits=$_POST['profit'];
    $losses=0;
    $update_idea=updateIdea($idea,$profits,$losses,$conn);
    $pay_benefits=payBenefits($conn,$profits,$idea);
    
    if ($update_idea=='success') {
      // header('Location: ../admin.php?message=idea updated successful');
       echo "<script>alert('profits updated successful')</script>";
    	admin();
    }else {
      // header('Location: ../admin.php?error=idea update failed');
      echo "<script>alert('profits update failed')</script>";
    	admin();
     }
    
    
  }
 elseif (isset($_POST['losses'])) {
    $idea=$_POST['idea'];
    $profits=0;
    $losses=$_POST['loss'];;
    $update_idea=updateIdea($idea,$profits,$losses,$conn);
    if ($update_idea=='success') {
      // header('Location: ../admin.php?message=idea updated successful');
       echo "<script>alert('losses updated successful')</script>";
    	admin();
    }else {
      // header('Location: ../admin.php?error=idea update failed');
       echo "<script>alert('losses update failed')</script>";
    	admin();
     }
    
    
  }

  /*
 *checking for investment idea responding to an ajax call
 */
 elseif (isset($_GET['idea'])) {
   $idea=$_GET['idea'];
   $status=$_GET['status'];
   if ($status=='profits' || $status=='losses') {
     $check_idea=checkForIdea($idea,$conn);
     if ($check_idea=='idea unknown') {
       echo "Idea name doesnt exist";
   }
  }elseif ($status=='new') {
    $check_idea=checkForIdea($idea,$conn);
     if ($check_idea=='idea exists') {
       echo "Idea name already taken";
      }
  }
 }

 #processing idea votes
 elseif(isset($_GET['deal'])){
   $deal=$_GET['deal'];
   $username=$_GET['username'];
   $command=$_GET['command'];
   $insert_vote=handleVote($deal,$username,$command,$conn);
   echo $insert_vote;
 }
 elseif (isset($_GET['id'])) {
   $command=$_GET['id'];
   $value=$_GET['value'];
   $votes_number=countVotes($command,$value,$conn);
   echo $votes_number;
 }elseif (isset($_GET['adminreports'])) {
 	adminreports();
 }elseif (isset($_GET['admin'])) {
 	admin();
 }
 else{
 	indexpage();
 }
}
?>

