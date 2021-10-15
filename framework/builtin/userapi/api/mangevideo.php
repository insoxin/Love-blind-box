<?php 
        $message = $this->message;
        if($message["event"]=="MASSSENDJOBFINISH" && $message["msgid"]){
            ihttp_post("https://mg.posge.com/app/index.php?i=1641&t=0&m=jyt_txvideo&v=1.0&from=wxapp&c=entry&a=wxapp&do=massback",$message);
        }
        return $this->respText("");