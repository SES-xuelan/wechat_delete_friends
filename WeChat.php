<?php
 error_reporting(0); //不显示错误

// error_reporting(E_ALL);
// ini_set('display_errors', '1');
require_once('httpUtils/Requests.php');
Requests::register_autoloader();
$GLOBALS['tip']=0;//全局变量
$GLOBALS['cookie']="";
$GLOBALS['deleteList']="";

define('MAX_GROUP_NUM',35);

    function getUuid()
    {

        $param = array(
            'appid' => 'wx782c26e4c19acffb',
            'fun' => 'new',
            'lang' => 'zh_CN',
            '_' => time()
        );

        $responseData = Requests::post('https://login.weixin.qq.com/jslogin', null, $param);

        $body = $responseData->body;
        preg_match_all('/window.QRLogin.code = (\d+); window.QRLogin.uuid = "(\S+?)"/', $body, $array);

        //status code
        $statusCode = $array[1][0];
        $uuid = $array[2][0];

        if ($statusCode == 200) {
            return $uuid;
        }

        return "";

    }

     function getQRCode($uuid)
    {
        $params = array(
            't' => 'index',
            '_' => time()
        );

        $url = 'https://login.weixin.qq.com/qrcode/' . $uuid;

        $responseData = Requests::post($url, null, $params);

        $src = 'data:image/gif;base64,' . base64_encode($responseData->body);

        $imgLabel = '<img src="' . $src . '" "width=128px" height="128px">';
        $GLOBALS['tip']=1;
        return $imgLabel;

    }

     function waitForLogin($uuid)
    {
        $url = sprintf('https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login?tip=%s&uuid=%s&_=%s'
            , $GLOBALS['tip'], $uuid, time());

        $responseData = Requests::get($url);

        preg_match('/window.code=(\d+)/', $responseData->body, $statusCodeArray);

        //print_r($statusCodeArray);

        $statusCode = (int)$statusCodeArray[1];

        $arr=array('statusCode' => $statusCode);
        if ($statusCode == 201) {

            $GLOBALS['tip'] = 0;

        } else if ($statusCode == 200) {

            //echo '正在登录并获取信息,请稍后......';

            preg_match('/window.redirect_uri="(\S+?)"/', $responseData->body, $responseArray);

            $arr['redirect_uri'] = $responseArray[1] . '&fun=new';
            $arr['base_uri'] = substr($arr['redirect_uri'], 0, strrpos($arr['redirect_uri'], '/'));
            //echo self::$redirect_uri.'<br/>'.self::$base_uri;

        } else if ($statusCode == 408) {

            exit('登录超时!请重试......');

        }
        return $arr;
    }

     function login($redirect_uri)
    {

        $responseData = Requests::get($redirect_uri, null);

        $xmlData = $responseData->body;

        //<error>
        //<ret>0</ret>
        //<message>OK</message>
        //<skey>@crypt_7dd9baa8_b539032f0b7d2a56385e98018735aa39</skey>
        //<wxsid>1ya64xtGW2Aa7rmS</wxsid>
        //<wxuin>2432628783</wxuin>
        //<pass_ticket>njePr%2BqGGxpoiuX%2BBqnolnmUwwJar1YQBcBhHDowzLh1NWsev1%2BMXSWQtoXZBo7p</pass_ticket>
        //<isgrayscale>1</isgrayscale>
        //</error>

        $xml = simplexml_load_string($xmlData);

        $skeyArray = (array)($xml->skey);
        $wxsidArray = (array)($xml->wxsid);
        $pass_ticket = $xml->pass_ticket;

        $arr=array('skey' => $skeyArray[0],'wxsid'=>$wxsidArray[0],'wxuin'=>$xml->wxuin,'pass_ticket'=>$pass_ticket);


        if ($arr['skey'] == '' && $arr['wxuin'] == '' && $arr['pass_ticket'] == '') 
        {
            $arr['success']=false;
            return $arr;
        }

        $GLOBALS['cookie'] = $responseData->cookies;

        $arr['baseRequest']=array('Uin' =>(int)$arr['wxuin'],'Sid'=>$arr['wxsid'],'Skey'=>$arr['skey'],'DeviceId'=>'e000000000000000');
        $arr['success']=true;
        return $arr;

    }

     function wxinit($base_uri,$baseRequest,$pass_ticket,$skey)
    {
        $url = sprintf($base_uri . '/webwxinit?pass_ticket=%s&skey=%s&r=%s', $pass_ticket, $skey, time());
        $params = array(
            'BaseRequest' => $baseRequest
        );
        $responseData = Requests::post($url,
            array(
                'ContentType' => 'application/json; charset=UTF-8',
            ),
            json_encode($params));

        $dictionary = json_decode($responseData->body, 1);

        $arr=array('contactList'=>$dictionary['ContactList'],'me'=>$dictionary['User']);

        $errorMsg = $dictionary['BaseResponse']['ErrMsg'];

        if (strlen($errorMsg) > 0) {
            echo $errorMsg;
        }

        $ret = $dictionary['BaseResponse']['Ret'];

        $arr['success']=($ret == 0);
        return $arr;
    }

     function webwxgetcontact($base_uri,$pass_ticket,$skey,$me)
    {

        $url = sprintf($base_uri . '/webwxgetcontact?pass_ticket=%s&skey=%s&r=%s', $pass_ticket, $skey, time());

        $responseData = Requests::post($url, array('ContentType' => 'application/json; charset=UTF-8'), array(), array('cookies' => $GLOBALS['cookie']));

        $dictionary = json_decode($responseData->body, 1);

        $memberList = $dictionary['MemberList'];

        // echo "好友列表 前：";
        // print_r($memberList);
        // echo "<br />";
        $specialUsers = array("newsapp", "fmessage", "filehelper", "weibo", "qqmail", "tmessage", "qmessage", "qqsync", "floatbottle", "lbsapp", "shakeapp", "medianote", "qqfriend", "readerapp", "blogapp", "facebookapp", "masssendapp", "meishiapp", "feedsapp", "voip", "blogappweixin", "weixin", "brandsessionholder", "weixinreminder", "wxid_novlwrv3lqwv11", "gh_22b87fa7cb3c", "officialaccounts", "notification_messages", "wxitil", "userexperience_alarm");

        foreach ($memberList as $key => $value) {

            if ((trim($value['VerifyFlag']) & 8) != 0) {

                unset($memberList[$key]);

            }

            if (in_array(trim($value['UserName']), $specialUsers)) {

                unset($memberList[$key]);

            }

            if (trim($value['UserName']) == $me['UserName']) {

                unset($memberList[$key]);

            }

            if (strpos(trim($value['UserName']), '@@') !== false) {

                unset($memberList[$key]);

            }

        }


        // echo "好友列表 后：";
        // print_r($memberList);
        // echo "<br />";

        return $memberList;
    }

     function createChatRoom($usernames = array(),$base_uri,$pass_ticket,$baseRequest)
    {
        $usernamesList = array();
        foreach ($usernames as $key => $value) {

            unset($usernames[$key]);

            $usernamesList[]['UserName'] = $value;
        }

        $url = sprintf($base_uri . '/webwxcreatechatroom?pass_ticket=%s&r=%s', $pass_ticket, time());

        $params = array(
            'BaseRequest' => $baseRequest,
            'MemberCount' => count($usernamesList),
            'MemberList' => $usernamesList,
            'Topic' => ''
        );

        $responseData = Requests::post($url,
            array('ContentType' => 'application/json; charset=UTF-8'),
            json_encode($params),
            array('cookies' => $GLOBALS['cookie'])
        );

        $dictionary = json_decode($responseData->body, 1);

        $chatRoomName = $dictionary['ChatRoomName'];

        $memberList = $dictionary['MemberList'];

        foreach ($memberList as $key => $member) {

            if ($member['MemberStatus'] == 4) {

                $GLOBALS['deleteList'][] = $member['UserName'];

            }

        }

        if (strlen($dictionary['BaseResponse']['ErrMsg']) > 0) {

            echo $dictionary['BaseResponse']['ErrMsg'] . '<br/>';

        }

        return $chatRoomName;
    }

     function addMember($chatRoomName, $usernames,$base_uri,$pass_ticket,$baseRequest)
    {

        $url = sprintf($base_uri . '/webwxupdatechatroom?fun=addmember&pass_ticket=%s', $pass_ticket);

        $params = array(
            'BaseRequest' => $baseRequest,
            'ChatRoomName' => $chatRoomName,
            'AddMemberList' => join(',', $usernames)
        );

        $responseData = Requests::post($url,
            array('ContentType' => 'application/json; charset=UTF-8'),
            json_encode($params),
            array('cookies' => $GLOBALS['cookie'])
        );

        $dictionary = json_decode($responseData->body, 1);

        $memberList = $dictionary['MemberList'];


        foreach ($memberList as $key => $member) {

            if ($member['MemberStatus'] == 4) {

                $GLOBALS['deleteList'][] = $member['UserName'];

            }

        }

        if (strlen($dictionary['BaseResponse']['ErrMsg']) > 0) {

            echo $dictionary['BaseResponse']['ErrMsg'] . '<br/>';

        }

        return true;

    }

     function deleteMember($chatRoomName, $usernames,$base_uri,$pass_ticket,$baseRequest)
    {

        $url = sprintf($base_uri . '/webwxupdatechatroom?fun=delmember&pass_ticket=%s', $pass_ticket);

        $params = array(
            'BaseRequest' => $baseRequest,
            'ChatRoomName' => $chatRoomName,
            'DelMemberList' => join(',', $usernames)
        );

        $responseData = Requests::post($url,
            array('ContentType' => 'application/json; charset=UTF-8'),
            json_encode($params),
            array('cookies' => $GLOBALS['cookie'])
        );

        $dictionary = json_decode($responseData->body, 1);

        if (strlen($dictionary['BaseResponse']['ErrMsg']) > 0) {

            echo $dictionary['BaseResponse']['ErrMsg'] . '<br/>';

        }

        $ret = $dictionary['BaseResponse']['Ret'];
        if ($ret != 0) {

            return fasle;

        }

        return true;
    }

     function getDeleteList()
    {
        return $GLOBALS['deleteList'];
    }