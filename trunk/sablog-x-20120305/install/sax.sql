DROP TABLE IF EXISTS sablog_articles;
CREATE TABLE sablog_articles (
  articleid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  description text NOT NULL,
  content mediumtext NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  views int(10) unsigned NOT NULL DEFAULT '0',
  comments mediumint(8) unsigned NOT NULL DEFAULT '0',
  attachments tinyint(4) NOT NULL DEFAULT '0',
  closecomment tinyint(1) NOT NULL DEFAULT '0',
  closetrackback tinyint(1) NOT NULL DEFAULT '0',
  visible tinyint(1) NOT NULL DEFAULT '1',
  stick tinyint(1) NOT NULL DEFAULT '0',
  readpassword varchar(20) NOT NULL DEFAULT '',
  alias varchar(200) NOT NULL DEFAULT '',
  pingurl tinytext NOT NULL,
  PRIMARY KEY (articleid),
  KEY dateline (dateline),
  KEY visible (visible,dateline),
  KEY archives (visible,dateline),
  KEY admin (dateline),
  KEY stick (stick)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS sablog_attachments;
CREATE TABLE sablog_attachments (
  attachmentid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  articleid mediumint(8) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  filename varchar(100) NOT NULL DEFAULT '',
  filetype varchar(50) NOT NULL DEFAULT '',
  filesize int(10) unsigned NOT NULL DEFAULT '0',
  downloads mediumint(8) unsigned NOT NULL DEFAULT '0',
  filepath varchar(255) NOT NULL DEFAULT '',
  thumb_filepath varchar(255) NOT NULL DEFAULT '',
  thumb_width smallint(6) NOT NULL DEFAULT '0',
  thumb_height smallint(6) NOT NULL DEFAULT '0',
  isimage tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (attachmentid),
  KEY display (articleid,isimage)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS sablog_comments;
CREATE TABLE sablog_comments (
  commentid int(10) unsigned NOT NULL AUTO_INCREMENT,
  comment_parent int(10) NOT NULL,
  articleid mediumint(8) unsigned NOT NULL DEFAULT '0',
  author varchar(40) NOT NULL,
  email varchar(40) NOT NULL,
  url varchar(75) NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  content mediumtext NOT NULL,
  ipaddress varchar(16) NOT NULL DEFAULT '',
  `type` enum('comment','trackback') NOT NULL DEFAULT 'comment',
  visible tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (commentid),
  KEY ipaddress (ipaddress),
  KEY displayorder (articleid,visible),
  KEY dateline (dateline)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS sablog_links;
CREATE TABLE sablog_links (
  linkid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  url varchar(200) NOT NULL DEFAULT '',
  note varchar(200) NOT NULL DEFAULT '',
  visible tinyint(1) NOT NULL DEFAULT '0',
  home tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (linkid),
  KEY displayorder (visible)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS sablog_metas;
CREATE TABLE sablog_metas (
  mid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  slug varchar(200) NOT NULL,
  `type` enum('category','tag','link_category') NOT NULL,
  description varchar(200) NOT NULL DEFAULT '',
  count smallint(6) unsigned NOT NULL DEFAULT '0',
  displayorder smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (mid),
  KEY slug (slug),
  KEY displayorder (displayorder)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


INSERT INTO sablog_metas VALUES(1, '默认分类', 'default', 'category', '', 0, 0);

DROP TABLE IF EXISTS sablog_relationships;
CREATE TABLE sablog_relationships (
  cid mediumint(8) unsigned NOT NULL DEFAULT '0',
  mid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid,mid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS sablog_searchindex;
CREATE TABLE sablog_searchindex (
  searchid int(11) unsigned NOT NULL AUTO_INCREMENT,
  keywords varchar(255) NOT NULL DEFAULT '',
  searchstring varchar(255) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  expiration int(10) unsigned NOT NULL DEFAULT '0',
  sortby varchar(32) NOT NULL DEFAULT '',
  orderby varchar(4) NOT NULL DEFAULT '',
  totals smallint(6) unsigned NOT NULL DEFAULT '0',
  ids text NOT NULL,
  ipaddress varchar(16) NOT NULL DEFAULT '',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (searchid),
  KEY dateline (dateline),
  KEY sortby (sortby),
  KEY orderby (orderby)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sablog_settings;
CREATE TABLE sablog_settings (
  title varchar(50) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (title)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO sablog_settings VALUES('name', 'Sablog-X');
INSERT INTO sablog_settings VALUES('url', '');
INSERT INTO sablog_settings VALUES('description', '又一个基于Sablog-X构建的博客');
INSERT INTO sablog_settings VALUES('icp', '');
INSERT INTO sablog_settings VALUES('templatename', 'default');
INSERT INTO sablog_settings VALUES('article_order', 'dateline');
INSERT INTO sablog_settings VALUES('related_shownum', '10');
INSERT INTO sablog_settings VALUES('related_title_limit', '0');
INSERT INTO sablog_settings VALUES('show_calendar', '0');
INSERT INTO sablog_settings VALUES('show_categories', '1');
INSERT INTO sablog_settings VALUES('hottags_shownum', '20');
INSERT INTO sablog_settings VALUES('show_archives', '1');
INSERT INTO sablog_settings VALUES('recentcomment_num', '10');
INSERT INTO sablog_settings VALUES('recentcomment_limit', '12');
INSERT INTO sablog_settings VALUES('show_statistics', '1');
INSERT INTO sablog_settings VALUES('show_debug', '1');
INSERT INTO sablog_settings VALUES('audit_comment', '1');
INSERT INTO sablog_settings VALUES('seccode', '0');
INSERT INTO sablog_settings VALUES('comment_order', '1');
INSERT INTO sablog_settings VALUES('article_comment_num', '15');
INSERT INTO sablog_settings VALUES('comment_min_len', '4');
INSERT INTO sablog_settings VALUES('comment_max_len', '6000');
INSERT INTO sablog_settings VALUES('commentlist_num', '10');
INSERT INTO sablog_settings VALUES('comment_post_space', '20');
INSERT INTO sablog_settings VALUES('seccode_adulterate', '1');
INSERT INTO sablog_settings VALUES('attachments_dir', 'attachments');
INSERT INTO sablog_settings VALUES('attachments_save_dir', '2');
INSERT INTO sablog_settings VALUES('attachments_thumbs', '1');
INSERT INTO sablog_settings VALUES('attachments_thumbs_size', '500x500');
INSERT INTO sablog_settings VALUES('display_attach', '1');
INSERT INTO sablog_settings VALUES('remote_open', '1');
INSERT INTO sablog_settings VALUES('watermark', '0');
INSERT INTO sablog_settings VALUES('watermark_size', '300x300');
INSERT INTO sablog_settings VALUES('waterpos', '2');
INSERT INTO sablog_settings VALUES('watermarktrans', '100');
INSERT INTO sablog_settings VALUES('pos_padding', '5');
INSERT INTO sablog_settings VALUES('server_timezone', '8');
INSERT INTO sablog_settings VALUES('comment_timeformat', 'Y-m-d, g:i A');
INSERT INTO sablog_settings VALUES('recent_comment_timeformat', 'm-d');
INSERT INTO sablog_settings VALUES('close', '0');
INSERT INTO sablog_settings VALUES('close_note', '');
INSERT INTO sablog_settings VALUES('gzipcompress', '0');
INSERT INTO sablog_settings VALUES('showmsg', '0');
INSERT INTO sablog_settings VALUES('closereg', '0');
INSERT INTO sablog_settings VALUES('censoruser', '');
INSERT INTO sablog_settings VALUES('enable_trackback', '1');
INSERT INTO sablog_settings VALUES('audit_trackback', '1');
INSERT INTO sablog_settings VALUES('trackback_life', '0');
INSERT INTO sablog_settings VALUES('seccode_angle', '1');
INSERT INTO sablog_settings VALUES('seccode_ttf', '1');
INSERT INTO sablog_settings VALUES('title_keywords', '');
INSERT INTO sablog_settings VALUES('meta_keywords', '');
INSERT INTO sablog_settings VALUES('meta_description', '');
INSERT INTO sablog_settings VALUES('wap_article_limit', '500');
INSERT INTO sablog_settings VALUES('wap_enable', '0');
INSERT INTO sablog_settings VALUES('banip_enable', '1');
INSERT INTO sablog_settings VALUES('ban_ip', '');
INSERT INTO sablog_settings VALUES('spam_enable', '1');
INSERT INTO sablog_settings VALUES('spam_words', '虚拟主机,域名注册,出租网,六合彩,铃声下载,手机铃声,和弦铃声,手机游戏,免费铃声,彩铃,网站建设,操你妈,rinima,日你妈,αngel,鸡,操,鸡吧,小姐,fuck,胡锦涛,温家宝,胡温,李洪志,法轮,民运,反共,专制,专政,独裁,极权,中共,共产,共党,六四,民主,人权,毛泽东,中国政府,中央政府,游行示威,天安门,达赖,他妈的,我操,强奸,法轮,[url,<a href,法輪功');
INSERT INTO sablog_settings VALUES('spam_url_num', '3');
INSERT INTO sablog_settings VALUES('spam_content_size', '2000');
INSERT INTO sablog_settings VALUES('tb_spam_level', 'strong');
INSERT INTO sablog_settings VALUES('rss_enable', '1');
INSERT INTO sablog_settings VALUES('rss_num', '15');
INSERT INTO sablog_settings VALUES('rss_ttl', '30');
INSERT INTO sablog_settings VALUES('permalink', '1');
INSERT INTO sablog_settings VALUES('close_comment', '0');
INSERT INTO sablog_settings VALUES('rss_all_output', '0');
INSERT INTO sablog_settings VALUES('sitemap', '1');
INSERT INTO sablog_settings VALUES('recentarticle_num', '10');
INSERT INTO sablog_settings VALUES('recentarticle_limit', '0');
INSERT INTO sablog_settings VALUES('maxpages', '1000');
INSERT INTO sablog_settings VALUES('show_n_p_title', '1');
INSERT INTO sablog_settings VALUES('seccode_color', '1');
INSERT INTO sablog_settings VALUES('seccode_size', '1');
INSERT INTO sablog_settings VALUES('seccode_shadow', '1');
INSERT INTO sablog_settings VALUES('article_shownum', '5');
INSERT INTO sablog_settings VALUES('dateconvert', '1');
INSERT INTO sablog_settings VALUES('article_timeformat', 'Y-m-d, g:i A');
INSERT INTO sablog_settings VALUES('show_avatar', '1');
INSERT INTO sablog_settings VALUES('avatar_size', '36');
INSERT INTO sablog_settings VALUES('avatar_level', 'G');
INSERT INTO sablog_settings VALUES('randarticle_num', '0');
INSERT INTO sablog_settings VALUES('jumpwww', '0');
INSERT INTO sablog_settings VALUES('comment_email_reply', '1');
INSERT INTO sablog_settings VALUES('stat_code', '');


DROP TABLE IF EXISTS sablog_statistics;
CREATE TABLE sablog_statistics (
  article_count int(11) unsigned NOT NULL DEFAULT '0',
  comment_count int(11) unsigned NOT NULL DEFAULT '0',
  tag_count int(11) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO sablog_statistics VALUES(0, 0, 0);


DROP TABLE IF EXISTS sablog_stylevars;
CREATE TABLE sablog_stylevars (
  stylevarid mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(200) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  description varchar(200) NOT NULL,
  visible tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (stylevarid),
  UNIQUE KEY title (title)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sablog_users;
CREATE TABLE sablog_users (
  userid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(20) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL,
  logincount smallint(6) unsigned NOT NULL DEFAULT '0',
  loginip varchar(16) NOT NULL DEFAULT '',
  logintime int(10) unsigned NOT NULL DEFAULT '0',
  email varchar(40) NOT NULL,
  url varchar(75) NOT NULL,
  articles int(11) unsigned NOT NULL DEFAULT '0',
  regdateline int(10) unsigned NOT NULL DEFAULT '0',
  regip varchar(16) NOT NULL DEFAULT '',
  groupid smallint(4) unsigned NOT NULL DEFAULT '0',
  lastpost int(10) unsigned NOT NULL DEFAULT '0',
  lastip varchar(16) NOT NULL,
  lastvisit int(10) unsigned NOT NULL,
  lastactivity int(10) unsigned NOT NULL,
  PRIMARY KEY (userid),
  UNIQUE KEY username (username),
  KEY groupid (groupid,userid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
