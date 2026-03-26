tinyMCEPopup.requireLangPack();

var LinkDialog = {
	preInit : function() {
		var url;

		if (url = tinyMCEPopup.getParam("external_link_list_url"))
			document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');
	},

	init : function() {
		var f = document.forms[0], ed = tinyMCEPopup.editor;

		// Setup browse button
		document.getElementById('hrefbrowsercontainer').innerHTML = getBrowserHTML('hrefbrowser', 'href', 'file', 'theme_advanced_link');
		if (isVisible('hrefbrowser'))
			document.getElementById('href').style.width = '180px';

		this.fillClassList('class_list');
		this.fillFileList('link_list', 'tinyMCELinkList');
		this.fillTargetList('target_list');

		if (e = ed.dom.getParent(ed.selection.getNode(), 'A')) {
			f.href.value = ed.dom.getAttrib(e, 'href');
			f.linktitle.value = ed.dom.getAttrib(e, 'title');
			f.insert.value = ed.getLang('update');
			selectByValue(f, 'link_list', f.href.value);
			selectByValue(f, 'target_list', ed.dom.getAttrib(e, 'target'));
			selectByValue(f, 'class_list', ed.dom.getAttrib(e, 'class'));
		}
	},

	update : function () {
		var f  = document.forms[0],
			ed = tinyMCEPopup.editor,
			dom = ed.dom,
			e, href, target, cssClass, selectedHtml;

		tinyMCEPopup.restoreSelection();

		href     = f.href.value.replace(/ /g, '%20');
		target   = f.target_list ? getSelectValue(f, "target_list") : null;
		cssClass = f.class_list ? getSelectValue(f, "class_list") : null;

		// ищем существующую ссылку вокруг каретки
		e = dom.getParent(ed.selection.getNode(), 'A');

		// если URL пустой Ч удал€ем ссылку (если была) и выходим
		if (!href) {
			if (e) {
				var b = ed.selection.getBookmark();
				dom.remove(e, 1);
				ed.selection.moveToBookmark(b);
				tinyMCEPopup.execCommand("mceEndUndoLevel");
			}
			tinyMCEPopup.close();
			return;
		}

		// берЄм HTML, который был выделен
		selectedHtml = ed.selection.getContent({ format : 'html' });

// 1) если нет выделени€ »Ћ» оно потер€лось Ц пробуем вз€ть текст из пол€ "«аголовок"
// 2) если заголовок пустой Ц берЄм сам URL
		if (!selectedHtml) {
			if (f.linktitle && f.linktitle.value) {
				selectedHtml = f.linktitle.value;
			} else {
				selectedHtml = href;
			}
		}


		if (e) {
			// уже есть <a> Ц просто обновл€ем атрибуты и текст
			dom.setAttribs(e, {
				href   : href,
				title  : f.linktitle.value,
				target : target,
				'class': cssClass
			});
			e.innerHTML = selectedHtml;
		} else {
			// создаЄм Ќќ¬”ё ссылку Ѕ≈« #mce_temp_url#
			var attrs = ' href="' + href + '"';
			if (target && target !== '_self') attrs += ' target="' + target + '"';
			if (cssClass) attrs += ' class="' + cssClass + '"';
			if (f.linktitle.value) attrs += ' title="' + f.linktitle.value + '"';

			ed.execCommand('mceInsertContent', false, '<a' + attrs + '>' + selectedHtml + '</a>');
		}

		tinyMCEPopup.execCommand("mceEndUndoLevel");
		tinyMCEPopup.close();
	},


	checkPrefix : function(n) {
		if (n.value && Validator.isEmail(n) && !/^\s*mailto:/i.test(n.value) && confirm(tinyMCEPopup.getLang('advanced_dlg.link_is_email')))
			n.value = 'mailto:' + n.value;

		if (/^\s*www\./i.test(n.value) && confirm(tinyMCEPopup.getLang('advanced_dlg.link_is_external')))
			n.value = 'http://' + n.value;
	},

	fillFileList : function(id, l) {
		var dom = tinyMCEPopup.dom, lst = dom.get(id), v, cl;

		l = window[l];

		if (l && l.length > 0) {
			lst.options[lst.options.length] = new Option('', '');

			tinymce.each(l, function(o) {
				lst.options[lst.options.length] = new Option(o[0], o[1]);
			});
		} else
			dom.remove(dom.getParent(id, 'tr'));
	},

	fillClassList : function(id) {
		var dom = tinyMCEPopup.dom, lst = dom.get(id), v, cl;

		if (v = tinyMCEPopup.getParam('theme_advanced_styles')) {
			cl = [];

			tinymce.each(v.split(';'), function(v) {
				var p = v.split('=');

				cl.push({'title' : p[0], 'class' : p[1]});
			});
		} else
			cl = tinyMCEPopup.editor.dom.getClasses();

		if (cl.length > 0) {
			lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('not_set'), '');

			tinymce.each(cl, function(o) {
				lst.options[lst.options.length] = new Option(o.title || o['class'], o['class']);
			});
		} else
			dom.remove(dom.getParent(id, 'tr'));
	},

	fillTargetList : function(id) {
		var dom = tinyMCEPopup.dom, lst = dom.get(id), v;

		lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('not_set'), '');
		lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('advanced_dlg.link_target_same'), '_self');
		lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('advanced_dlg.link_target_blank'), '_blank');

		if (v = tinyMCEPopup.getParam('theme_advanced_link_targets')) {
			tinymce.each(v.split(','), function(v) {
				v = v.split('=');
				lst.options[lst.options.length] = new Option(v[0], v[1]);
			});
		}
	}
};

LinkDialog.preInit();
tinyMCEPopup.onInit.add(LinkDialog.init, LinkDialog);
