
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>微信查看谁删除了你--By SES雪蓝</title>
</head>
<body>
<font color="#FF0000" ><b>"清清吧。不用回。试试吧，复制我发的消息，找到微信里的设置，通用，群发助手，全选，粘贴复制的信息发送就行，谁的发送失败了，就是把你拉黑了，你再扔掉那些尸体就OK啦[调皮]"</b></font><br />
你还在用这种落后的、打扰到别人的方法来清理微信好友么？来，看我口型~~~~你OUT啦！<br />
今天就让我来给你提供一种不打扰别人、又能知道谁把你删了的方法吧~<br /><br />

本程序是根据 0x5e 大神提供的python版写的PHP版，python版源代码：<a href="https://github.com/0x5e/wechat-deleted-friends">请点击这里</a><br />原理就是新建群组,如果加不进来就是被删好友了(不要在群组里讲话,别人是看不见的)
<br />
<h4>目前未解决的问题</h4>
①微信限制了接口，接口访问限制会出现【操作太频繁，请稍后再试】，不清楚接口的限制策略是什么,有的同学能用有的不能用<br />
②URLError (网络异常未处理)<br />
③最终会遗留下一个只有自己的群组,需要手工删一下<br />

<h3><font color="#FF00FF" >警告：查询结果可能会引起心理不适，请谨慎使用</font></h3>
<form action="login.php" method="post">
	<input type="submit" value="我明白了,开始使用">
</form>
</body>
</html>