<?php
namespace Bluejacket\Connector;
use Bluejacket\Core\AbstractClass as AbstractClass;
class SSH extends AbstractClass
{
    public function connect(){
        $this->$connection = ssh2_connect($this->hostname, (isset($this->port) ? $this->port : 22));
        if(isset($this->username) && isset($this->password)){
            ssh2_auth_password($this->connection, $this->username, $this->password);
        }
        if(isset($this->username) && isset($this->public) && isset($this->private)){
            ssh2_auth_pubkey_file($this->connection, $this->username, $this->public, $this->private);
        }
        return self;
    }

    public function exec($command){
        return ssh2_exec($this->connection, $command);
    }

    public function scp($remoteFolder, $localFolder){
        $com ="ls $remoteFolder"; 
        $stream = ssh2_exec($this->connection, $com); 
        stream_set_blocking($stream,true); 
        $cmd=fread($stream,4096); 

        $arr=explode("\n",$cmd); 

        $totalFiles=sizeof($arr); 

        for($i=0;$i<$totalFiles;$i++){ 
            $fileName=trim($arr[$i]); 
            if($fileName!=''){ 
                $remoteFile=$remoteFolder.$fileName;        
                $localFile=$localFolder.$fileName; 
                
                if(ssh2_scp_recv($this->connection, $remoteFile,$localFile)){ 
                    $copied[] = $localFile;
                } 
            } 
        } 

        fclose($stream);
        return (is_array($copied) ? $copied : false);
    }
}