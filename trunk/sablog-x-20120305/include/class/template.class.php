<?php

/**
 *	[Site Engine] (C)2001-2008 Cnteacher@126.com
 * 	This is NOT a freeware, use is subject to license terms
 *
 *	$RCSfile: class_template.php,v $
 * 	$Revision: 1.6 $
 *  	$Date: 2007/08/20 13:01:19 $
 *	V2: use <?php ? > It's more fast than 'echo <<<EOT'
 */

class TemplateEngine{

	var $html	= '';
	var $tplcode	= array();
	var $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
	var $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\-\>[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*(\[[a-zA-Z0-9_\-\.\\\"\\\'\[\]\$\x7f-\xff]+\])*)";

	function TemplateEngine(){
		// I have nothing to do
	}

	function build($tplfile, $objfile) {
		$this->load_template($tplfile);
		$this->parse();
		$this->write_objfile($objfile);
		$this->clear_memory();
	}

	function clear_memory() {
		$this->html = '';
		$this->tplcode = array();
	}

	function parse() {

		// 清理标记和tab
		$this->html = preg_replace("/([\n\r]+)\t+/s", "\\1", $this->html);
		$this->html = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $this->html);

		// 处理语言包
		$this->html = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->languagevar('\\1')", $this->html);

		// 处理标准模板标记
		$this->html = preg_replace("/[\n\r\t]*\{((template\s|eval\s|echo\s|loop\s|if\s|elseif\s|else(?=\})|\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)|\/if|\/loop)[^}]*?)\}[\n\r\t]*/ies", "\\\$this->transform_lable('\\1')", $this->html);

		// 处理未使用标记的变量
		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\-\>[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*(\[[a-zA-Z0-9_\-\.\\\"\\\'\[\]\$\x7f-\xff]+\])*)";
		$this->html = preg_replace("/{$this->var_regexp}/ies", "\$this->addquote('<?php echo \\1; ?>')", $this->html);

		// 处理常量字符

		$this->html = preg_replace("/\{{$this->const_regexp}\}/s", "<?php echo \\1; ?>", $this->html);

		// 替换模板标记为正常内容
		if($count = count($this->tplcode)) {
			for($i = 0; $i <= $count; $i++) {
				$this->html = str_replace("<[\tCTP_TPL_N_{$i}\t]>", $this->tplcode[$i], $this->html);
			}
		}

		// 整个模板组合
		$this->html = '<?php if(!defined(\'SABLOG_ROOT\')) exit(\'Access Denied\'); ?>'.$this->html;

		// 清理冗余的标记
		$this->html = str_replace(' ?><?php ', '', $this->html);

		return true;
	}

	function transform_lable($str) {
		if($str == '/if') {
			$str = '<?php } ?>';
		} elseif($str == '/loop') {
			$str = '<?php }}?>';
		} elseif($str == 'else') {
			$str = '<?php } else { ?>';
		} elseif(substr($str, 0, 2) == 'if') {
			$str = preg_replace("/if\s+(.*)/ies", "\$this->addquote('<?php if(\\1) { ?>')", $str);
		} elseif(substr($str, 0, 6) == 'elseif') {
			$str = preg_replace("/elseif\s+(.*)/ies", "\$this->addquote('<?php } elseif(\\1) { ?>')", $str);
		} elseif(substr($str, 0, 8) =='template') {
			$str = '<?php include template(\''.trim(substr($str, 9)).'\'); ?>';
		} elseif(substr($str, 0, 4) == 'loop') {
			$str = preg_replace("/loop\s+(\S+)\s+(\S+)\s+(\S+)/ies", "\$this->addquote('<?php if(is_array(\\1)){\nforeach(\\1 as \\2 => \\3) { ?>'", $str);
			$str = preg_replace("/loop\s+(\S+)\s+(\S+)/ies", "\$this->addquote('<?php if(is_array(\\1)){\n\tforeach(\\1 as \\2) {\n ?>')", $str);
		} elseif(substr($str, 0, 4) == 'eval') {
			$str = '<?php '.trim(substr($str, 5)).'; ?>';
		} elseif(substr($str, 0, 4) == 'echo') {
			$str = '<?php '.trim($str).'; ?>';
		} elseif(substr($str, 0, 1) == '$') {
			$str = '<?php echo '.$this->addquote($str).'; ?>';
		} else {
			$str = '{'.$str.'}';
		}
		$this->tplcode[] = $str;
		$count = count($this->tplcode) - 1;
		return "<[\tCTP_TPL_N_{$count}\t]>";
	}

	function addquote($var) {
		return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\]/s", "['\\1']", $var));
	}

	function removequote($var) {
		return preg_replace("/\[\\'([a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\\'\]/s", "[\\1]", $var);
	}

	function languagevar($var) {
		global $language,$self;
		$return = $self[$var] = isset($language[$var]) ? $language[$var] : "!$var!";
		return $return;
	}

	function write_objfile($objfile) {
		if($this->writefile($objfile, $this->html) === false) {
			exit('Template obj file: '.basename($this->objfile). ' can\'t be writen');
		}
		return true;
	}

	function load_template($filename) {
		$this->html = $this->loadfile($filename);
		if($this->html === false) {
			exit('Template file: '.basename($this->tplfile). ' not found or can\'t be read');
		}
		return true;
	}
}
?>