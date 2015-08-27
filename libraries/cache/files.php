<?php
namespace libraries\cache;
use Bottle\Bottle;
/*
   +----------------------------------------------------------------------+
   | 文本缓存类                                                   		  |
   +----------------------------------------------------------------------+
   | Notes:
   +----------------------------------------------------------------------+
 */
class Files {

    /**
     * 生成cache file
     */ 
	public function build($pathName,Array $data) {
    $toStr = '<?php'.PHP_EOL . 'return ' .var_export($data,TRUE).';';
    $fileSize = file_put_contents('data/cache/'.$pathName.'.php',$toStr,LOCK_EX);
    if(!$fileSize){
      Bottle::throwException('data/cache/'.$pathName.'.php'. ' failed to open stream: Permission denied');
    }
	}

    /**
     * 删除cache file
     */ 
    public function delete() {

    }
} 