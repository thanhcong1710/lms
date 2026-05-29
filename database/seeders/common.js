String.prototype.replaceAll = function(target, replacement) {
	var reg = new RegExp(target, 'g');
	return this.replace(reg, replacement); // 정규식 사용
}

function getContextPath() {
	var hostIndex = location.href.indexOf( location.host ) + location.host.length;
	return location.href.substring( hostIndex, location.href.indexOf('/', hostIndex + 1) );
};

var isEmpty = function(val) {
	return (val === undefined || val == null || val.length <= 0 || /^\s*$/.test(val)) ? true : false;
}

var nullToEmptyStr = function(val) {
	return isEmpty(val) ? '' : val;
}

var _NVL = function(val,str) {
	return isEmpty(val) ? str : val;
}

var drawTable = function(id, option) {
	var _options;

	_options = $.extend({
		searching : true
		, lengthChange : false
		, info       : true
		, pageLength : 10
		, processing : true
		, serverSide : true
		, ordering   : false
		, cache      : false
		, language : {
			paginate : {
				previous : "<i class='fa fa-angle-left'></i>"
				, next   : "<i class='fa fa-angle-right'></i>"
			}
			, info : $.i18n.prop('dataTables.language.info')
			, infoEmpty : $.i18n.prop('dataTables.language.infoEmpty')
			, zeroRecords : $.i18n.prop('dataTables.language.zeroRecords')
		}
	}, option);

	$('#'+id).DataTable(_options);

	$("#dataTb_filter").hide();
}

var execute = function(option) {
	if(isEmpty(option.url))
		return;

	var _options;

	_options = $.extend({
		url       : option.url
		, type    : (isEmpty(option.type) ? 'POST' : option.type)
		, cache   : false
		, success : function(result) {
			if ('function' === typeof option.callback) {
				option.callback(result);
			}
		}
		, error   : function(result, status, error) {
			if ('function' === typeof option.error) {
				option.error(option);
			}
		}
	}, option);

	$.ajax(_options);

}

var ComSubmit = function(formId) {
	this.formId = isEmpty(formId) == true ? "frm" : formId;
	this.url = "";

	var frm = $("<form id='"+this.formId+"'></form>");
	//frm.appendTo("body");
	$(document.body).append(frm);

	this.setUrl = function setUrl(url){
		this.url = url;
	};

	this.addParam = function addParam(key, value){
		frm.append($("<input type='hidden' name='"+key+"' id='"+key+"' value='"+value+"' >"));
	};

	this.setTarget = function(target) {
		frm.attr("target",target);
	}

	this.submit = function submit(){
		frm.attr("action", this.url);
		frm.attr("method", "post");
		frm.submit();
	};
}

var validateElement = function(type, obj) {
	if((event.keyCode >= 35 && event.keyCode <= 40) || event.keyCode == 16) return;

	if(type == 'alphabet') {
		validateAlphabet(obj);
	}
	else if(type == 'email') {
		validateEmail(obj);
	}
	else if(type == 'phone') {
		validatePhone(obj);
	}
	else if(type == 'number') {
		validateNumber(obj);
	}
}

var validateAlphabet = function(obj) {
	$("#"+obj.id).val($.trim(obj.value).toLowerCase().replace(/[^a-zA-Z0-9\.-_]/gi,""));
}

var validateEmail = function(obj) {
	$("#"+obj.id).val($.trim(obj.value).replace(/[^a-zA-Z0-9\.-_@]/gi,""));
}

var validatePhone = function(obj) {
	$("#"+obj.id).val($.trim(obj.value).replace(/[^0-9\-]/gi,""));
}

var validateNumber = function(obj) {
	$("#"+obj.id).val($.trim(obj.value).replace(/[^0-9]/gi,""));
}

var checkValidate = function(elmtArr) {
	var rtn = {
		idx : -1
	};
	for (var i = 0 ; i < elmtArr.length ; i++) {
		if(isEmpty($(elmtArr[i]).val())) {
			rtn = {
				idx : i
			}
			rtn;
			break;
		}
	}
	return rtn;
}

var escapeHTML = function (unsafe_str) {
	if(isEmpty(unsafe_str))
		return '';

	return unsafe_str
	.replace(/&/g, '&amp;')
	.replace(/</g, '&lt;')
	.replace(/>/g, '&gt;')
	.replace(/\"/g, '&quot;')
	.replace(/\'/g, '&#39;')
	.replace(/\//g, '&#x2F;');
}

var restoreHTML = function(str) {
	if(isEmpty(str))
		return '';

	return str
	.replace(/&amp;/g  ,"&" )
	.replace(/&lt;/g   ,"<" )
	.replace(/&gt;/g   ,">" )
	.replace(/&quot;/g ,"\"")
	.replace(/&#39;/g  ,"'" )
	.replace(/&#x2F;/g ,"\/")
	.replace(/&Agrave;/g,"À")
	.replace(/&Aacute;/g,"Á")
	.replace(/&Acirc;/g ,"Â")
	.replace(/&Atilde;/g,"Ã")
	.replace(/&Auml;/g  ,"Ä")
	.replace(/&Aring;/g ,"Å")
	.replace(/&agrave;/g,"à")
	.replace(/&aacute;/g,"á")
	.replace(/&acirc;/g ,"â")
	.replace(/&atilde;/g,"ã")
	.replace(/&auml;/g  ,"ä")
	.replace(/&aring;/g ,"å")
	.replace(/&AElig;/g ,"Æ")
	.replace(/&aelig;/g ,"æ")
	.replace(/&szlig;/g ,"ß")
	.replace(/&Ccedil;/g,"Ç")
	.replace(/&ccedil;/g,"ç")
	.replace(/&Egrave;/g,"È")
	.replace(/&Eacute;/g,"É")
	.replace(/&Ecirc;/g ,"Ê")
	.replace(/&Euml;/g  ,"Ë")
	.replace(/&egrave;/g,"è")
	.replace(/&eacute;/g,"é")
	.replace(/&ecirc;/g ,"ê")
	.replace(/&euml;/g  ,"ë")
	.replace(/&#131;/g  ,"ƒ")
	.replace(/&Igrave;/g,"Ì")
	.replace(/&Iacute;/g,"Í")
	.replace(/&Icirc;/g ,"Î")
	.replace(/&Iuml;/g  ,"Ï")
	.replace(/&igrave;/g,"ì")
	.replace(/&iacute;/g,"í")
	.replace(/&icirc;/g ,"î")
	.replace(/&iuml;/g  ,"ï")
	.replace(/&Ntilde;/g,"Ñ")
	.replace(/&ntilde;/g,"ñ")
	.replace(/&Ograve;/g,"Ò")
	.replace(/&Oacute;/g,"Ó")
	.replace(/&Ocirc;/g ,"Ô")
	.replace(/&Otilde;/g,"Õ")
	.replace(/&Ouml;/g  ,"Ö")
	.replace(/&ograve;/g,"ò")
	.replace(/&oacute;/g,"ó")
	.replace(/&ocirc;/g ,"ô")
	.replace(/&otilde;/g,"õ")
	.replace(/&ouml;/g  ,"ö")
	.replace(/&Oslash;/g,"Ø")
	.replace(/&oslash;/g,"ø")
	.replace(/&#140;/g  ,"Œ")
	.replace(/&#156;/g  ,"œ")
	.replace(/&#138;/g  ,"Š")
	.replace(/&#154;/g  ,"š")
	.replace(/&Ugrave;/g,"Ù")
	.replace(/&Uacute;/g,"Ú")
	.replace(/&Ucirc;/g ,"Û")
	.replace(/&Uuml;/g  ,"Ü")
	.replace(/&ugrave;/g,"ù")
	.replace(/&uacute;/g,"ú")
	.replace(/&ucirc;/g ,"û")
	.replace(/&uuml;/g  ,"ü")
	.replace(/&#181;/g  ,"µ")
	.replace(/&#215;/g  ,"×")
	.replace(/&Yacute;/g,"Ý")
	.replace(/&#159;/g  ,"Ÿ")
	.replace(/&yacute;/g,"ý")
	.replace(/&yuml;/g  ,"ÿ");
}

var urlEncode = function (str) {
	str = (str + '').toString();
	return encodeURIComponent(str)
		.replace(/!/g, '%21')
		.replace(/'/g, '%27')
		.replace(/\(/g, '%28')
		.replace(/\)/g, '%29')
		.replace(/\*/g, '%2A')
		.replace(/%20/g, '+');
}

var urlDecode = function (str) {
	return decodeURIComponent((str + '')
		.replace(/%(?![\da-f]{2})/gi, function() {
			return '%25';
		})
		.replace(/\+/g, '%20'));
}

function rawUrlEncode(str) {
	str = (str + '').toString();
	return encodeURIComponent(str)
		.replace(/!/g, '%21')
		.replace(/'/g, '%27')
		.replace(/\(/g, '%28')
		.replace(/\)/g, '%29')
		.replace(/\*/g, '%2A');
}

function rawUrlDecode(str) {
	return decodeURIComponent((str + '')
		.replace(/%(?![\da-f]{2})/gi, function() {
			return '%25';
		}));
}

var setSearch = function(cd, text, searchFlag) {
	setSearchCols(cd);
	$("#searchText").val(text);
	if(searchFlag)
		$("#btnSearch").click();
}

var setDropdown = function(cd, text, searchFlag) {
	$(document).find("."+cd).prev().find('.output_select').html(text);
	$(document).find("."+cd + ">li>a").each(function() {
		if($(this).html() == text) {
			$("#" + util.camelize("s_"+cd)).val($(this).attr("data-cd"));
			return false;
		}
	});
}

// parameter set dropdown
var setParams = function(name, value) {
	$(document).find("."+name + ">li>a").each(function() {
		if($(this).attr("data-cd") == value) {
			$(this).parent().parent().parent().find('.output_select').html($(this).html());
			return false;
		}
	});
}

var util = {
	camelize : function(string) {
		if (this._isNumerical(string)) {
			return string;
		}
		string = string.replace(/[\-_\s]+(.)?/g, function(match, chr) {
			return chr ? chr.toUpperCase() : '';
		});
		return string.substr(0, 1).toLowerCase() + string.substr(1);
	}
	, _isNumerical : function(obj) {
		obj = obj - 0;
		return obj === obj;
	}
	, isNumberKey: function(evt) {
		var e = evt || window.event; // for trans-browser compatibility
		var charCode = e.which || e.keyCode;
		if (charCode > 31 && !((charCode >= 48 && charCode <= 57) || (charCode >= 96 && charCode <= 105)))
		return false;
		if (e.shiftKey) return false;
		return true;
	}
}

var commonSelectNone = function(list) {
	return CommonSelect(list, "");
}
var commonSelectAll = function(list) {
	return CommonSelect(list, "ALL");
}

var commonSelectChoice = function(list) {
	return CommonSelect(list, "CHOICE");
}

var CommonSelect = function(list, type) {
	var tmpHtml = "";

	if(type == 'ALL') {
		tmpHtml += "<option>전체</option>";
	} else if(type == 'CHOICE') {
		tmpHtml += "<option>선택</option>";
	}

	for ( var data in list ) {
		tmpHtml += "<option value=" + data.stuSeq + ">" + data.stuNm + "(" + data.stuBirthDt + ")</option>";
	}
}

var nextmove = function(current) {
	var next = Number(current) +1;
	var lastNum = next / 32;
	if(!numCheck(lastNum)) {
		if($('input[idx="'+current+'"]').val() != '' ) {
			$('input[idx="'+next+'"]').select().focus();
			return;
		}
	}
}

//숫자 정수 체크
var numCheck = function (obj){
	var num_check = /^[0-9]*$/;
	if(!num_check.test(obj)) {
		return;
	}
}

var getDate = function() {
	var dt = Date.today();
	return dt.toString('yyyy-MM-dd');
}

var getAddMonth = function(startDt,add) {
	var dt = Date.parse(startDt).add(add).month().add(-1).days();
	return dt.toString('yyyy-MM-dd');
}

var replaceBR = function(str) {
	return str.replace(/\n/gi,"<br>");
}

var reverseBR = function(str) {
	return str.replace(/<br>/gi,"\n");
}

var getTeacherIcon = function(head) {
	if(head == 'Y') {
		return "<i class='fa fa-user-plus' aria-hidden='true'></i> "
	} else {
		return "<i class='fa fa-user' aria-hidden='true'></i> "
	}
}

var checkByte  = function (_this, max_byte) {
	var str = _this.val();var str_len = str.length;var rbyte = 0;var rlen = 0;var one_char = "";var str2 = "";
	for(var i = 0 ; i < str_len ; i++) {one_char = str.charAt(i);if(escape(one_char).length > 4) {rbyte += 2;} else {rbyte++;}if(rbyte <= max_byte){rlen = i + 1;}}
	if(rbyte > max_byte) {toastr['info']($.i18n.prop('common.alert.maxlength'));_this.val(str.substr(0,rlen));} else {$("#byteInfo").text(rbyte);}
}