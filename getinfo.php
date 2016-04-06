
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>微信查看谁删除了你--By SES雪蓝</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
</head>
<body>
<?php
include 'WeChat.php';
$loginInfo=waitForLogin($_POST['uuid']);
//var_dump($loginInfo);

$base_uri=$loginInfo['base_uri'];
$info=login($loginInfo['redirect_uri']);
//var_dump($info);
if ($info['success']==false)
{
	exit("登录失败！请重试");
}

$initInfo=wxinit($base_uri,$info['baseRequest'],$info['pass_ticket'],$info['skey']);
//var_dump($initInfo);

$__m=webwxgetcontact($base_uri,$info['pass_ticket'],$info['skey'],$initInfo['me']);
$memberList = array_values($__m);
//var_dump($memberList);
$memberCount = count($memberList) - 1;

echo "你的微信里目前有 ".$memberCount." 个好友<br/>";

$groupNumber = ceil($memberCount/MAX_GROUP_NUM);

$chatRoomName = '';
for ($i = 0 ;$i < $groupNumber ;$i++){
    $usernames = array();
    $nicknames = array();

    for($j = 0 ;$j < MAX_GROUP_NUM ;$j++){

        if(($i * MAX_GROUP_NUM + $j) >= $memberCount){
            break;
        }
        $member = $memberList[$i + MAX_GROUP_NUM + $j];
        $usernames[] = $member['UserName'];
        $nicknames[] = $member['NickName'];
        //echo "当前的处理 NickName ".$member['NickName']."<br />";
    }
    //TODO
    if($chatRoomName == '')
    {
        $chatRoomName = createChatRoom($usernames,$base_uri,$info['pass_ticket'],$info['baseRequest']);
    }else{
        addMember($chatRoomName,$usernames,$base_uri,$info['pass_ticket'],$info['baseRequest']);
    }

    $is_success=deleteMember($chatRoomName,$usernames,$base_uri,$info['pass_ticket'],$info['baseRequest']);
}

echo '<br/>---------------:当前删除你的好友列表如下:---------------<br/>';

$deleteList = getDeleteList();

$resultNames = '';

if(empty($deleteList)){

    echo '没有任何人删除了你.';
    echo '<br /><br /><a href="./index.php">返回首页</a>';
    return;
}

foreach ($memberList as $key => $member){

    if(in_array($member['UserName'],$deleteList)){
        $r_name=$member['RemarkName'];
        if(trim($r_name)=='')
            $resultNames .= ' | '.$member['NickName'];
        else
            $resultNames .= ' | '.$member['NickName'].'('.$r_name.')';
    }

}

echo $resultNames.'<br/>';
echo '<br /><a href="./index.php">返回首页</a>';

?>

</body>
</html>