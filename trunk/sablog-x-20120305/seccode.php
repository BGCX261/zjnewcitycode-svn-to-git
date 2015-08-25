<?PHP
// ========================== 文件说明 ==========================//
// 本文件说明：验证码生成
// --------------------------------------------------------------//
// 本程序作者：angel
// --------------------------------------------------------------//
// 本程序版本：SaBlog-X Ver 2.0
// --------------------------------------------------------------//
// 本程序主页：http://www.sablog.net
// ==============================================================//

require_once './include/common.inc.php';

$refererhost = parse_url($_SERVER['HTTP_REFERER']);
$refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

if($refererhost['host'] != $_SERVER['HTTP_HOST'] || !$options['seccode']) {
//	exit('Access Denied');
}

$_SESSION['seccode'] = random(6, 1);

@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");

include_once SABLOG_ROOT.'include/class/seccode.class.php';
$code = new seccode();
$code->code = $_SESSION['seccode'];

$code->width = 150;
$code->height = 60;
$code->adulterate = $options['seccode_adulterate'];	//随机背景图形
$code->ttf = $options['seccode_ttf'];	//随机TTF字体
$code->angle = $options['seccode_angle'];	//随机倾斜度
$code->color = $options['seccode_color'];	//随机颜色
$code->size = $options['seccode_size'];	//随机大小
$code->shadow = $options['seccode_shadow'];	//文字阴影

$code->fontpath = SABLOG_ROOT.'include/fonts/';
$code->display();

?>