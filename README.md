# XDB
XDB，一个轻量级但不简单的文件数据库，用php编写，只要有php就可以用。
版本说明
-------
版本：1.0.1-alpha1，只实现了select,where,DB,showDB,showTBL,insert,update,delete，预计实现makeDB,makeTBL,makeUSER,deleteUSER,chpwd,chper<br>
目前还只能include引用，日后实现curl远程remote。<br>

如何使用？
-----
使用简单：修改config.ini.php，把data改成自己的目录（绝对路径），把/server/index.php中两个配置文件的include改成绝对路径，再include我们的/server/index.php，就可以new一个xdb，开始使用。<br>
默认passcode:1234    username:root     password:123456<br>
例如：<br>
"
<?php
  include("./server/index.php");
  $xdb = new xdb("1234","123456","root");
  echo "<pre>";
  if($xdb->getLogin()[0]){
    var_dump($xdb->DB("test")->where("%","%")->select("test"));
  }else{
    echo $xdb->getLogin[1];
  }
?>
"
