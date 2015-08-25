/*******************************************************************************
* KindEditor - WYSIWYG HTML Editor for Internet
* Copyright (C) 2006-2011 kindsoft.net
*
* @author Roddy <luolonghao@gmail.com>
* @site http://www.kindsoft.net/
* @licence http://www.kindsoft.net/license.php
*******************************************************************************/

KindEditor.plugin('image', function(K) {
	var self = this, name = 'image',
		imgPath = self.basePath + 'plugins/image/images/',
		lang = self.lang(name + '.');

	self.plugin.imageDialog = function(options) {
		var imageUrl = K.undef(options.imageUrl, 'http://'),
			imageWidth = K.undef(options.imageWidth, ''),
			imageHeight = K.undef(options.imageHeight, ''),
			imageTitle = K.undef(options.imageTitle, ''),
			imageAlign = K.undef(options.imageAlign, ''),
			clickFn = options.clickFn;
		var html = [
			'<div style="padding:10px 20px;">',
			//url or file
			'<div class="ke-dialog-row">',
			'<label for="keUrl" style="width:60px;">' + lang.remoteUrl + '</label>',
			'<input type="text" id="keUrl" class="ke-input-text" name="url" value="" style="width:200px;" />',
			'</div>',
			//size
			'<div class="ke-dialog-row">',
			'<label for="keWidth" style="width:60px;">' + lang.size + '</label>',
			lang.width + ' <input type="text" id="keWidth" class="ke-input-text ke-input-number" name="width" value="" maxlength="4" /> ',
			lang.height + ' <input type="text" class="ke-input-text ke-input-number" name="height" value="" maxlength="4" /> ',
			'<img class="ke-refresh-btn" src="' + imgPath + 'refresh.gif" width="16" height="16" alt="" style="cursor:pointer;" />',
			'</div>',
			//align
			'<div class="ke-dialog-row">',
			'<label style="width:60px;">' + lang.align + '</label>',
			'<input type="radio" name="align" class="ke-inline-block" value="" checked="checked" /> <img name="defaultImg" src="' + imgPath + 'align_top.gif" width="23" height="25" alt="" />',
			' <input type="radio" name="align" class="ke-inline-block" value="left" /> <img name="leftImg" src="' + imgPath + 'align_left.gif" width="23" height="25" alt="" />',
			' <input type="radio" name="align" class="ke-inline-block" value="right" /> <img name="rightImg" src="' + imgPath + 'align_right.gif" width="23" height="25" alt="" />',
			'</div>',
			//title
			'<div class="ke-dialog-row">',
			'<label for="keTitle" style="width:60px;">' + lang.imgTitle + '</label>',
			'<input type="text" id="keTitle" class="ke-input-text" name="title" value="" style="width:200px;" /></div>',
			'</div>',
			'</div>'
		].join('');
		var dialogWidth = 400;
			dialogHeight = 250;
		var dialog = self.createDialog({
			name : name,
			width : dialogWidth,
			height : dialogHeight,
			title : self.lang(name),
			body : html,
			yesBtn : {
				name : self.lang('yes'),
				click : function(e) {
					// insert remote image
					var url = K.trim(urlBox.val()),
						width = widthBox.val(),
						height = heightBox.val(),
						title = titleBox.val(),
						align = '';
					alignBox.each(function() {
						if (this.checked) {
							align = this.value;
							return false;
						}
					});
					if (url == 'http://' || K.invalidUrl(url)) {
						alert(self.lang('invalidUrl'));
						urlBox[0].focus();
						return;
					}
					if (!/^\d*$/.test(width)) {
						alert(self.lang('invalidWidth'));
						widthBox[0].focus();
						return;
					}
					if (!/^\d*$/.test(height)) {
						alert(self.lang('invalidHeight'));
						heightBox[0].focus();
						return;
					}
					clickFn.call(self, url, title, width, height, 0, align);
				}
			},
			beforeRemove : function() {
				widthBox.remove();
				heightBox.remove();
				refreshBtn.remove();
			}
		}),
		div = dialog.div;

		var urlBox = K('[name="url"]', div),
			widthBox = K('[name="width"]', div),
			heightBox = K('[name="height"]', div),
			refreshBtn = K('.ke-refresh-btn', div),
			titleBox = K('[name="title"]', div),
			alignBox = K('[name="align"]');

		var originalWidth = 0, originalHeight = 0;
		function setSize(width, height) {
			widthBox.val(width);
			heightBox.val(height);
			originalWidth = width;
			originalHeight = height;
		}
		refreshBtn.click(function(e) {
			var tempImg = K('<img src="' + urlBox.val() + '" />', self.edit.doc).css({
					position : 'absolute',
					visibility : 'hidden',
					top : 0,
					left : '1000px'
				});
			K(self.edit.doc.body).append(tempImg);
			setSize(tempImg.width(), tempImg.height());
			tempImg.remove();
		});
		widthBox.change(function(e) {
			if (originalWidth > 0) {
				heightBox.val(Math.round(originalHeight / originalWidth * parseInt(this.value, 10)));
			}
		});
		heightBox.change(function(e) {
			if (originalHeight > 0) {
				widthBox.val(Math.round(originalWidth / originalHeight * parseInt(this.value, 10)));
			}
		});
		urlBox.val(options.imageUrl);
		setSize(options.imageWidth, options.imageHeight);
		titleBox.val(options.imageTitle);
		alignBox.each(function() {
			if (this.value === options.imageAlign) {
				this.checked = true;
				return false;
			}
		});
		urlBox[0].focus();
		urlBox[0].select();
		return dialog;
	};
	self.plugin.image = {
		edit : function() {
			var img = self.plugin.getSelectedImage();
			self.plugin.imageDialog({
				imageUrl : img ? img.attr('data-ke-src') : 'http://',
				imageWidth : img ? img.width() : '',
				imageHeight : img ? img.height() : '',
				imageTitle : img ? img.attr('title') : '',
				imageAlign : img ? img.attr('align') : '',
				clickFn : function(url, title, width, height, border, align) {
					self.exec('insertimage', url, title, width, height, border, align).hideDialog().focus();
				}
			});
		},
		'delete' : function() {
			self.plugin.getSelectedImage().remove();
		}
	};
	self.clickToolbar(name, self.plugin.image.edit);
});
