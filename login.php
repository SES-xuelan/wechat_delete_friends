

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>微信查看谁删除了你--By SES雪蓝</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
</head>
<body>
<?php
include 'WeChat.php';
$uuid=getUuid();
if ($uuid=="")
{
	exit("获取UUID失败!请重试");
}
//echo $uuid;
echo "打开微信扫一扫，然后扫这个二维码<br />";
echo getQRCode($uuid);
?>
<form action="getinfo.php" method="post">
	<input type="hidden" name="uuid" id="uuid" value="<?php echo $uuid; ?>">
	<br />
	扫描完之后，请在手机上确认登录，确认好了之后请点击“获取信息”按钮：<input type="submit" value="获取信息">
</form>
</body>
</html></html>