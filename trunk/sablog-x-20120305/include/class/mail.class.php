<?php

if(!defined('SABLOG_ROOT')) {
	exit('Access Denied');
}

class MailSend {
	private $subject;
	private $toaddr;
	private $fromaddr;
	private $content;
	private $header;
	private $domain;//邮箱域
	private $msg;

	private $boundary;
	private $uniqid;

	private $eol; //每行末尾所加的换行符类型

	function __construct(){
		$this->getEOT();//生成结尾换行符
		$this->getUniq_id();
		$this->header='';
		$this->msg='';
	}

	public function setFromaddr($fromaddr) {
		$this->fromaddr = trim($fromaddr);
	}

	public function setSubject($subject) {
		$this->subject = mb_convert_encoding(trim($subject),'UTF-8','auto');
	}

	public function setToaddr($toaddr) {
		$this->toaddr = trim($toaddr);
	}

	public function setContent($content) {
		$this->content = mb_convert_encoding(trim($content),'UTF-8','auto');
	}

	public function setDomain($domain) {
		$this->domain = $domain;//输入的值为‘@domain.com’
	}

	/*
	* 根据系统类型设置换行符
	*/
	private function getEOT() {
		if (strtoupper ( substr ( PHP_OS, 0, 3 ) == 'WIN' )) {
			$this->eol = "\r\n";
		} elseif (strtoupper ( substr ( PHP_OS, 0, 3 ) == 'MAC' )) {
			$this->eol= "\r";
		} else {
			$this->eol = "\n";
		}
	}

	private function getBoundary(){
		$this->boundary= '--'.substr(md5(time().rand(1000,2000)),0,16);
	}

	private function getUniq_id(){
		$this->uniqid= md5(microtime().time().rand(1,100));
	}

	private function outputCommonHeader(){
		$this->header .= 'From: '.$this->fromaddr.$this->eol;
		//$this->header .= 'To: '.$this->toaddr.$this->eol;
		//$this->header .= 'Subject: '.$this->subject.$this->eol;
		$this->header .= 'Message-ID: <'.$this->uniqid.$this->domain.'>'.$this->eol;
		$this->header .= 'MIME-Version: 1.0'.$this->eol;
		$this->header .= 'Reply-To: '.$this->fromaddr.$this->eol;
		$this->header .= 'Return-Path: '.$this->fromaddr.$this->eol;
		$this->header .= 'X-Mailer: Xmail System'.$this->eol;
		$this->header .= 'Content-Disposition: inline'.$this->eol;
	}
	/*
	* 不带附件
	*/
	private function mailSimple(){
		$this->header .= 'Content-type: text/html; charset=UTF-8'.$this->eol;
		$this->header .= 'Content-Transfer-Encoding: 8bit'.$this->eol;
		$this->msg = $this->content;
		if(mail($this->toaddr,$this->topic,$this->msg,$this->header)){
			return 1;
		}else{
			return 0;
		}
	}

	public function send(){
		if(!empty($this->toaddr) && !empty($this->subject) && !empty($this->content)) {
			$this->outputCommonHeader();
			return $this->mailSimple();
		} else {
			return 0;
		}
	}
}

?>