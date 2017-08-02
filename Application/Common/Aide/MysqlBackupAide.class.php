<?php

// +----------------------------------------------------------------------
// | Author: 左边 （加群：366504956(刚建，欢迎)  交流thinkphp下微信开发）
// +----------------------------------------------------------------------


namespace Common\Aide;
use \Think\Db;
use \sinacloud\sae\Storage as Storage;

class MysqlBackupAide {

    private $path;
    private $isCompress;
    private $content;
    private $dbName;
    private $error;
    private $sign = '/*++$*/';
    private $db;

    const DIR_SEP = DIRECTORY_SEPARATOR;

    public function __construct($db = null) {
        $this->path = C('BACKUP_PATH').'Databak';
        //默认为TP的数据库链接对象；
        if ($db == null){
            $dsn = 'mysql://'.C('DB_USER').':'.C('DB_PWD').'@'.C('DB_HOST').':'.C('DB_PORT').'/'.C('DB_NAME');
			$this->db = Db::getInstance($dsn);
        }else {
			$this->db = $db;
        }
    }
    /**
     * ----------------------------------------------
     * | 设置新的数据库链接对象(PDO对象)
     * | @时间: 2016年11月20日 上午6:57:05
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function setDb($db){
    	$this->db = $db;
    	return $this;
    }

    public function setDBName($dbName) {
        $this->dbName = $dbName;
        return $this;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public function setIsCompress($isCompress){
        $this->isCompress = $isCompress;
        return $this;
    }

    private function getFile($fileName) {

        if(strtolower(STORAGE_TYPE) == 'sae'){
            $s = new Storage();        
            $r = json_encode($s -> getObject(strtolower( $this->path ), $fileName) );
            $r = json_decode($r,true);
            return $r['body'];

        }
        $fileName = $this->path . self::DIR_SEP . $fileName;
        if (is_file($fileName)) {
            $ext = strrchr($fileName, '.');
            if ($ext == '.sql') {
                return file_get_contents($fileName);
            } elseif ($ext == '.gz') {
                return implode('', gzfile($fileName));
            } else {
                $this->error = '_无法识别的文件格式!';
                return;
            }
        } else {
            $this->error = '文件不存在!';
            return;
        }
    }


    private function setFile($content) {

        $recognize = $this->dbName;
        $fileName = $recognize . '_' . date('YmdHis') . '_' . uniqid('') . '.sql';

        if(strtolower(STORAGE_TYPE) == 'sae'){
            $s = new Storage();

            $root_path = strtolower($this->$path);
        //不存在就创建
            $pathArr = $s->listBuckets();
            if(!in_array($root_path,$pathArr) ){
                $s->putBucket($root_path,'.r:*');
            }
        //写入
            $path = $s->putObject($content, $root_path, $fileName);
            if($path !== true){
                $this->error = "写入文件失败";
                return;
            }
            return $path;
        }

        $fileName = $this->path . self::DIR_SEP .$fileName;
        $path = $this->cetPath($this->path);

        if ($path !== true) {
            $this->error = "无法创建备份目录,目录 '$path'";
            return;
        }
        if ($this->isCompress == 0) {
            if (!file_put_contents($fileName, $content, LOCK_EX)) {
                $this->error = '写入文件失败,请检查磁盘空间或者权限!';
                return;
            }
            return true;
        } else {
            if (function_exists('gzwrite')) {
                $fileName .= '.gz';
                $gz = gzopen($fileName, 'wb');
                if ($gz) {
                    $gw = gzwrite($gz, $content);
                    gzclose($gz);
                    return $gw;
                } else {
                    $this->error = '写入文件失败,请检查磁盘空间或者权限!';
                    return;
                }
            } else {
                $this->error = '没有开启gzip扩展!';
                return;
            }
        }
    }

    private function cetPath($file_path) {
        if (!is_dir($file_path)){
        	return  mkdir($file_path, 0777, true);
        }
        return true;
    }


    //备份
    public function backup(){
        $db = $this->db;
        $tables  = $db->query('SHOW TABLE STATUS');
        $tables  = array_map('array_change_key_case', $tables);
        $sql = '/* This file is created by MySQLReback ' . date('Y-m-d H:i:s') . ' */';
        foreach ($tables as $value) {
            $table = $value['name'];            
            $result = $db->query("SHOW CREATE TABLE `{$table}`");
            $create = $result[0]['create table'];
            $sql .= "\r\n /* 创建表结构 {$table} */";
            $sql .= "\r\n DROP TABLE IF EXISTS {$table};". $this->sign ." {$create};" . $this->sign;

            $result = $db->query("SELECT COUNT(*) AS count FROM `{$table}`");
            $count  = $result['0']['count'];
            if($count){
                $sql .= "\r\n /* 插入数据 {$table} */";

                $result = $db->query("SELECT * FROM {$table}");

                foreach ($result as $row) {
                    $row = array_map('addslashes', $row);
                    $sql .= "\r\n INSERT INTO {$table} VALUES ( '"  . str_replace( array("\r","\n") , array('\r','\n') ,implode("', '", $row) ) . "' );".$this->sign;
                }
            }

        }

        if ($sql) {
            return $this->setFile($sql);
        }else{
            $this->error = '数据为空';
        }
        return;

    }

    //删除备份
    public function remove($fileName){
        if(strtolower(STORAGE_TYPE) == 'sae'){
            $s = new Storage();
            $r = $s->deleteObject(strtolower($this->path), $fileName);
            if(!$r){
                $this->error = '删除失败';
                return;
            }
            return true;
        }
        if(!@unlink($this->path . self::DIR_SEP . $fileName)){
            $this->error = '删除失败';
            return;
        }
        return true;
    }

    //表优化
    public function optimize(){
        if(strtolower(STORAGE_TYPE) == 'sae'){
            $this->error='SAE数据库不支持表优化';
            return ;
        }
        $db = $this->db;
        $list  = $db->query('SHOW TABLE STATUS');
        $list  = array_map('array_change_key_case', $list);
        foreach ($list as $key => $value) {
            $tables[] = $value['name'];
        }

        if($tables) {
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list[] = $db->execute("OPTIMIZE TABLE `{$tables}`");
            }
        }
        return $list;
    }

    //恢复
    public function recover($fileName) {
        $content = $this->getFile($fileName);
        if (!$content) {
            if($this->error)return;
            return '数据为空';
        }
        $content = explode($this->sign, $content);
        $db = $this->db;
        foreach ($content as $i => $sql) {
            $sql = trim($sql);
            if (!empty($sql)){

                $res = $db->execute($sql);

                if($res){
                    $rs['qty']++;
                }else{
                    $rt[] = $sql;
                }
            }
        }
        $rs['error'] = $rt;
        return $rs;
    }

    public function DownloadFile($fileName) {
        ob_end_clean();
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');

        if(strtolower(STORAGE_TYPE) == 'sae'){
            $s = new Storage();        
            $r = json_encode($s -> getObject(strtolower( $this->path ), $fileName) );
            $r = json_decode($r,true);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo $r['body'];

        }else{
            $fileName = $this->path . self::DIR_SEP . $fileName;
            $stat = stat($this->path . self::DIR_SEP . $filename);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . $stat['size']);
            readfile($fileName);
        }

    }

    public function dataList() {

        if(strtolower(STORAGE_TYPE) == 'sae'){
            $s = new Storage();
            $f = $s->getBucket( strtolower($this->path) );
            foreach ($f as $v) {
                $rt['filename'] = $v['name'];
                $rt['filetime'] = date('Y-m-d H:i:s',strtotime($v['last_modified']) );
                $rt['filesize'] = $v['bytes'];
                $FileAndFolderAyy[] = $rt;
            }

        }else{
            $FilePath = opendir($this->path);
            while (false !== ($filename = readdir($FilePath))) {
                if ($filename!="." && $filename!=".."){
                    $stat = stat($this->path .'/'. $filename);
                    $rt['filename'] = $filename;
                    $rt['filetime'] = date('Y-m-d H:i:s', $stat['mtime'] );
                    $rt['filesize'] = $stat['size'];
                    $FileAndFolderAyy[] = $rt;
                }
            }
        }
        $Order == 0 ? sort($FileAndFolderAyy) : rsort($FileAndFolderAyy);
        return $FileAndFolderAyy;
    }

    /**
     * ----------------------------------------------
     * | 更新sql语句的方法
     * | @时间: 2016年11月17日 上午11:06:35
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function updateSql($filename, $sparator = '\n'){
    	if (file_exists($filename)){
    		$content = file_get_contents($filename);
    	}else {
    		$this->error = "数据库更新文件不存在";
    		return false;
    	}
    	//统一换行符
    	$content = preg_replace('/(\r)|(\r\n)|(\n)/', $sparator, $content);
    	//去除注释内容
    	$content = preg_replace('/\/\*(.*)?\*\//U', '', $content);
    	//按照行号分割内容
    	$content = explode($sparator, $content);
    	//出去空行内容
    	$content = array_filter($content);
    	
    	$db = $this->db;
    	$ok = $err = 0;
    	//循环执行sql语句；
    	foreach ($content as $i => $sql) {
    		$sql = trim($sql);
    		if (!empty($sql)){
    			$res = $db->execute($sql);
    			$res !== false ? $ok++: $err++;
    			if($res === false){
    				file_put_contents(CONFIG_PATH.'sql.log',date('Y-m-d H:i:s--').$sql.'------------error----------'.PHP_EOL, FILE_APPEND);
    			}else{
    				file_put_contents(CONFIG_PATH.'sql.log',date('Y-m-d H:i:s--').$sql.'------------ok----------'.PHP_EOL, FILE_APPEND);
    			}
    		}
    	}
    	if ( $err !== 0 ){
    		$this->error = '更新数据库出错...';
    		return  false;
    	}else {
    		/**
    		 * ---------------------------------------------------
    		 * | 删除安装后的sql log
    		 * | @时间: 2017年1月16日 下午4:25:20
    		 * ---------------------------------------------------
    		 */
    		unlink(CONFIG_PATH.'sql.log');
    		return true;
    	}
    	
    }



    public function error(){
        return $this->error;
    }


}
