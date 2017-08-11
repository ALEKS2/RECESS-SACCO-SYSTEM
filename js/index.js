// function for updating the files session
    function updateFiles(){
        var xhttp;
        if (window.XMLHttpRequest) {
            xhttp= new XMLHttpRequest();
        }else{
            xhttp= new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.open("GET","index.php?value=update files",true);
        xhttp.send();
        
      }

    // function for checking investment idea
    function checkIdea(idea,status){
      if(idea!=''){
        $(function(){
            $('#idea_messenger1,#idea_messenger2,#new_idea_messenger').addClass('alert alert-danger');
        });
        var xhttp;
        if (window.XMLHttpRequest) {
            xhttp= new XMLHttpRequest();
        }else{
            xhttp= new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.onreadystatechange=function(){
            if (this.readyState==4 && this.status==200) {
                if (this.responseText==='Idea name already taken') {
                    document.getElementById('new_idea_messenger').innerHTML=this.responseText;
                }else{
                    if (this.responseText!='Idea name doesnt exist') {
                        $(function(){
                        $('#idea_messenger,#new_idea_messenger').removeClass('alert alert-danger');
                        });
                    }
                    if(status=='profits'){
                       document.getElementById('idea_messenger1').innerHTML=this.responseText;
                    }else if(status=='losses'){
                        document.getElementById('idea_messenger2').innerHTML=this.responseText;
                    }

                    document.getElementById('new_idea_messenger').innerHTML='';
                }
            }
        }
        xhttp.open("GET","index.php?idea="+idea+"&status="+status,true);
        xhttp.send();
      }else{
          $(function(){
             $('#idea_messenger1,#new_idea_messenger,#idea_messenger2').removeClass('alert alert-danger');
          });
          document.getElementById('idea_messenger1').innerHTML='';
          document.getElementById('idea_messenger2').innerHTML='';
          document.getElementById('new_idea_messenger').innerHTML='';
      }
    }

    // function for handling vote
    function Votes(deal,id,username){
       var command=document.getElementById(id).value;
       var xhttp;
        if (window.XMLHttpRequest) {
            xhttp= new XMLHttpRequest();
        }else{
            xhttp= new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.onreadystatechange=function(){
            if (this.readyState==4 && this.status==200) {
                if(this.responseText=='already approved'){
                    $(function(){
                      $('#approve_messemger').addClass('alert alert-danger');
                      $('#reject_messemger').removeClass('alert alert-danger');
                    });
                    document.getElementById('reject_messemger').innerHTML='';
                   document.getElementById('approve_messemger').innerHTML=this.responseText;
                }else if(this.responseText=='already rejected'){
                    $(function(){
                      $('#reject_messemger').addClass('alert alert-danger');
                      $('#approve_messemger').removeClass('alert alert-danger');
                    });
                    document.getElementById('approve_messemger').innerHTML='';
                   document.getElementById('reject_messemger').innerHTML=this.responseText;
                }else{
                     $(function(){
                        $('#approve_messemger,#reject_messemger').removeClass('alert alert-danger');
                        });
                    document.getElementById('approve_messemger').innerHTML='';
                    document.getElementById('reject_messemger').innerHTML='';
                }
            }
        }

        xhttp.open("GET","index.php?deal="+deal+"&username="+username+"&command="+command,true);
        xhttp.send();
    }
    // function for counting votes
    function voteCount(id,value){
       var xhttp;
       var newid;
       if (value=='likes') {
           newid='badgel'+id;
       }else if(value=='dislikes'){
           newid='badged'+id;
       }
       var command=document.getElementById(id).value;
        if (window.XMLHttpRequest) {
            xhttp= new XMLHttpRequest();
        }else{
            xhttp= new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.onreadystatechange=function(){
            if (this.readyState==4 && this.status==200) {
                 document.getElementById(newid).innerHTML=this.responseText;
            }
        }
        xhttp.open("GET","index.php?id="+command+"&value="+value,true);
        xhttp.send();
        
    }