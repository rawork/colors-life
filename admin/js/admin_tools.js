var jsUtils =
{
	arEvents: Array(),

	addEvent: function(el, evname, func)
	{
		if(el.attachEvent) // IE
			el.attachEvent("on" + evname, func);
		else if(el.addEventListener) // Gecko / W3C
			el.addEventListener(evname, func, false);
		else
			el["on" + evname] = func;
		this.arEvents[this.arEvents.length] = {'element': el, 'event': evname, 'fn': func};
	},

	removeEvent: function(el, evname, func)
	{
		if(el.detachEvent) // IE
			el.detachEvent("on" + evname, func);
		else if(el.removeEventListener) // Gecko / W3C
			el.removeEventListener(evname, func, false);
		else
			el["on" + evname] = null;
	},

	removeAllEvents: function(el)
	{
		for(var i in this.arEvents)
		{
			if(this.arEvents[i] && (el==false || el==this.arEvents[i].element))
			{
				jsUtils.removeEvent(this.arEvents[i].element, this.arEvents[i].event, this.arEvents[i].fn);
				this.arEvents[i] = null;
			}
		}
		if(el==false)
			this.arEvents.length = 0;
	 },

	GetRealPos: function(el)
	{
		if(!el || !el.offsetParent)
			return false;
		var res=Array();
		res["left"] = el.offsetLeft;
		res["top"] = el.offsetTop;
		var objParent = el.offsetParent;
		while(objParent && objParent.tagName != "BODY")
		{
			res["left"] += objParent.offsetLeft;
			res["top"] += objParent.offsetTop;
			objParent = objParent.offsetParent;
		}
		res["right"]=res["left"] + el.offsetWidth;
		res["bottom"]=res["top"] + el.offsetHeight;
		
		return res;
	},

	FindChildObject: function(obj, tag_name, class_name)
	{
		if(!obj)
			return null;
		var tag = tag_name.toUpperCase();
		var cl = (class_name? class_name.toLowerCase() : null);
		var n = obj.childNodes.length;
		for(var j=0; j<n; j++)
		{
			var child = obj.childNodes[j];
			if(child.tagName && child.tagName.toUpperCase() == tag)
				if(!class_name || child.className.toLowerCase() == cl)
					return child;
		}
		return null;
	},

	FindNextSibling: function(obj, tag_name)
	{
		if(!obj)
			return null;
		var o = obj;
		var tag = tag_name.toUpperCase();
		while(o.nextSibling)
		{
			var sibling = o.nextSibling;
			if(sibling.tagName && sibling.tagName.toUpperCase() == tag)
				return sibling;
			o = sibling;
		}
		return null;
	},

	FindPreviousSibling: function(obj, tag_name)
	{
		if(!obj)
			return null;
		var o = obj;
		var tag = tag_name.toUpperCase();
		while(o.previousSibling)
		{
			var sibling = o.previousSibling;
			if(sibling.tagName && sibling.tagName.toUpperCase() == tag)
				return sibling;
			o = sibling;
		}
		return null;
	},

	FindParentObject: function(obj, tag_name)
	{
		if(!obj)
			return null;
		var o = obj;
		var tag = tag_name.toUpperCase();
		while(o.parentNode)
		{
			var parent = o.parentNode;
			if(parent.tagName && parent.tagName.toUpperCase() == tag)
				return parent;
			o = parent;
		}
		return null;
	},

	IsIE: function()
	{
		return (document.attachEvent && !this.IsOpera());
	},

	IsOpera: function()
	{
		return (navigator.userAgent.toLowerCase().indexOf('opera') != -1);
	},

	ToggleDiv: function(div)
	{
		var style = document.getElementById(div).style;
		if(style.display!="none")
			style.display = "none";
		else
			style.display = "block";
		return (style.display != "none");
	},

	urlencode: function(s)
	{
		return escape(s).replace(new RegExp('\\+','g'), '%2B');
	},

	OpenWindow: function(url, width, height)
	{
		var w = screen.width, h = screen.height;
		if(this.IsOpera())
		{
			w = document.body.offsetWidth;
			h = document.body.offsetHeight;
		}
		window.open(url, '', 'status=no,scrollbars=yes,resizable=yes,width='+width+',height='+height+',top='+Math.floor((h - height)/2-14)+',left='+Math.floor((w - width)/2-5));
	},
	
	trim: function(s)
	{
		var r, re;
		re = /^[ \r\n]+/g;
		r = s.replace(re, "");
		re = /[ \r\n]+$/g;
		r = r.replace(re, "");
		return r;
	},
	
	False: function(){return false;},

	AlignToPos: function(pos, w, h)
	{
		var x = pos["left"], y = pos["bottom"];

		var body = document.body;
		if((body.clientWidth + body.scrollLeft) - (pos["left"] + w) < 0)
		{
			if(pos["right"] - w >= 0 )
				x = pos["right"] - w;
			else
				x = body.scrollLeft;
		}

		if((body.clientHeight + body.scrollTop) - (pos["bottom"] + h) < 0)
		{
			if(pos["top"] - h >= 0)
				y = pos["top"] - h;
			else
				y = body.scrollTop;
		}
		
		return {'left':x, 'top':y};
	}
}

/************************************************/

function JCFloatDiv() 
{
	var _this = this;
	this.floatDiv = null;
	this.x = this.y = 0;

	this.Show = function(div, left, top, dxShadow)
	{
		var zIndex = parseInt(div.style.zIndex);
		if(zIndex <= 0 || isNaN(zIndex))
			zIndex = 100;
		div.style.zIndex = zIndex;
		div.style.left = left + "px";
		div.style.top = top + "px";

		if(jsUtils.IsIE())
		{
			var frame = document.getElementById(div.id+"_frame");
			if(!frame)
			{
				frame = document.createElement("IFRAME");
				frame.src = "javascript:void(0)";
				frame.id = div.id+"_frame";
				frame.style.position = 'absolute';
				frame.style.zIndex = zIndex-1;
				document.body.appendChild(frame);
			}
			frame.style.width = div.offsetWidth + "px";
			frame.style.height = div.offsetHeight + "px";
			frame.style.left = div.style.left;
			frame.style.top = div.style.top;
			frame.style.visibility = 'visible';
		}

		/*shadow*/
		if(isNaN(dxShadow))
			dxShadow = 5;
		if(dxShadow > 0)
		{
			var img = document.getElementById(div.id+'_shadow');
			if(!img)
			{
				if(jsUtils.IsIE())
				{
		 			img = document.createElement("DIV");
		 			img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+theme_ref+"/img/shadow.png',sizingMethod='scale')";
				}
				else
				{
		 			img = document.createElement("IMG");
					img.src = theme_ref+'/img/shadow.png';
				}
				img.id = div.id+'_shadow';
				img.style.position = 'absolute';
				img.style.zIndex = zIndex-2;
				document.body.appendChild(img);
			}
			img.style.width = div.offsetWidth+'px';
			img.style.height = div.offsetHeight+'px';
			img.style.left = parseInt(div.style.left)+dxShadow+'px';
			img.style.top = parseInt(div.style.top)+dxShadow+'px';
			img.style.visibility = 'visible';
		}
	}
		
	this.Close = function(div)
	{
		if(!div)
			return;
		var sh = document.getElementById(div.id+"_shadow");
		if(sh)
			sh.style.visibility = 'hidden';

		var frame = document.getElementById(div.id+"_frame");
		if(frame)
			frame.style.visibility = 'hidden';
	}
		
	this.Move = function(div, x, y, dxShadow)
	{
		if(!div)
			return;
			
		var left = parseInt(div.style.left)+x;
		var top = parseInt(div.style.top)+y;
		div.style.left = left+'px';
		div.style.top = top+'px';

		this.AdjustShadow(div, dxShadow);
	}
	
	this.AdjustShadow = function(div, dxShadow)
	{
		var sh = document.getElementById(div.id+"_shadow");
		if(sh)
		{
			if(isNaN(dxShadow))
				dxShadow = 5;

			sh.style.width = div.offsetWidth+'px';
			sh.style.height = div.offsetHeight+'px';
			sh.style.left = parseInt(div.style.left)+dxShadow+'px';
			sh.style.top = parseInt(div.style.top)+dxShadow+'px';
		}

		var frame = document.getElementById(div.id+"_frame");
		if(frame)
		{
			frame.style.width = div.offsetWidth + "px";
			frame.style.height = div.offsetHeight + "px";
			frame.style.left = div.style.left;
			frame.style.top = div.style.top;
		}
	}

	this.StartDrag = function(e, div)
	{
		if(!e)
			e = window.event;
		this.x = e.clientX + document.body.scrollLeft;
		this.y = e.clientY + document.body.scrollTop;
		this.floatDiv = div;

		jsUtils.addEvent(document, "mousemove", this.MoveDrag);
		document.onmouseup = this.StopDrag;
		if(document.body.setCapture)
			document.body.setCapture();
		
		var b = document.body;
	    b.ondrag = jsUtils.False;
	    b.onselectstart = jsUtils.False;
	    b.style.MozUserSelect = _this.floatDiv.style.MozUserSelect = 'none';
	    b.style.cursor = 'move';
    }

	this.StopDrag = function(e)
	{
		if(document.body.releaseCapture)
			document.body.releaseCapture();
		
		jsUtils.removeEvent(document, "mousemove", _this.MoveDrag);
		document.onmouseup = null;
		this.floatDiv = null;

		var b = document.body;
		b.ondrag = null;
		b.onselectstart = null;
		b.style.MozUserSelect = _this.floatDiv.style.MozUserSelect = '';
	    b.style.cursor = '';
	}

	this.MoveDrag = function(e)
	{
		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;
		if(_this.x == x && _this.y == y)
			return;
	
		_this.Move(_this.floatDiv, (x - _this.x), (y - _this.y));
		_this.x = x;
		_this.y = y;
	}
}
var jsFloatDiv = new JCFloatDiv();

/************************************************/

function PopupMenu(id)
{
	var _this = this;
	this.menu_id = id;
	this.controlDiv = null;
	this.dxShadow = 5

	this.OnClose = null;

	this.Create = function(zIndex, dxShadow)
	{
		if(!isNaN(dxShadow))
			this.dxShadow = dxShadow;

		var div = document.createElement("DIV");
		div.id = this.menu_id;
		div.style.position = 'absolute';
		div.style.zIndex = zIndex;
		div.style.left = '-1000px';
		div.style.top = '-1000px';
		div.style.visibility = 'hidden';
		document.body.appendChild(div);
		
		div.innerHTML = 
			'<table cellpadding="0" cellspacing="0" border="0">'+
			'<tr><td class="popupmenu">'+
			'<table cellpadding="0" cellspacing="0" border="0" id="'+this.menu_id+'_items">'+
			'<tr><td></td></tr>'+
			'</table>'+
			'</td></tr>'+
			'</table>';
	}

	this.PopupShow = function(pos)
	{
		var div = document.getElementById(this.menu_id);
		if(!div)
			return;

		setTimeout(function(){jsUtils.addEvent(document, "click", _this.CheckClick)}, 10);
		jsUtils.addEvent(document, "keypress", _this.OnKeyPress);

		var w = div.offsetWidth;
		var h = div.offsetHeight;
		pos = jsUtils.AlignToPos(pos, w, h);

		div.style.width = w + 'px';
		div.style.visibility = 'visible';

		jsFloatDiv.Show(div, pos["left"], pos["top"], this.dxShadow);

	    div.ondrag = jsUtils.False;
	    div.onselectstart = jsUtils.False;
	    div.style.MozUserSelect = 'none';
	}

	this.PopupHide = function()
	{
		var div = document.getElementById(this.menu_id);
		if(div)
		{
			jsFloatDiv.Close(div);
			div.style.visibility = 'hidden';
		}

		if(this.OnClose)
			this.OnClose();

		this.controlDiv = null;
		jsUtils.removeEvent(document, "click", _this.CheckClick);
		jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
	}

	this.CheckClick = function(e)
	{
		var div = document.getElementById(_this.menu_id);
		if(!div)
			return;

		if(div.style.visibility != 'visible')
			return;

		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;

		/*menu region*/
		var posLeft = parseInt(div.style.left);
		var posTop = parseInt(div.style.top);
		var posRight = posLeft + div.offsetWidth;
		var posBottom = posTop + div.offsetHeight;
		if(x >= posLeft && x <= posRight && y >= posTop && y <= posBottom)
			return;

		if(_this.controlDiv)
		{
			var pos = jsUtils.GetRealPos(_this.controlDiv);
			if(x >= pos['left'] && x <= pos['right'] && y >= pos['top'] && y <= pos['bottom'])
				return;
		}
		_this.PopupHide();
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.PopupHide();
	},

	this.BuildItems = function(items)
	{
		if(!items || items.length == 0)
			return;

		var div = document.getElementById(this.menu_id);
		div.style.left='-1000px';
		div.style.top='-1000px';
		div.style.width='auto';

		var tbl = document.getElementById(this.menu_id+'_items');
		while(tbl.rows.length>0)
			tbl.deleteRow(0);

		var n = items.length;
		for(var i=0; i<n; i++)
		{
			var row = tbl.insertRow(-1);
			var cell = row.insertCell(-1);
			if(items[i]['SEPARATOR'])
			{
				cell.innerHTML =
					'	<table cellpadding="0" cellspacing="0" border="0" class="popupseparator">\n'+
					'		<tr><td><div class="empty"></div></td></tr>\n'+
					'	</table>\n';
			}
			else
			{
				cell.innerHTML =
					'	<table cellpadding="0" cellspacing="0" border="0" class="popupitem"'+(items[i]['DISABLED']!=true? ' onMouseOver="this.className=\'popupitem popupitemover\';" onMouseOut="this.className=\'popupitem\';" onClick="'+items[i]['ONCLICK']+'"':'')+'>\n'+
					'		<tr>\n'+
					'			<td class="gutter"'+(items[i]['ID']? ' id="'+items[i]['ID']+'"' : '')+'><div class="'+(items[i]['ICONCLASS']? items[i]['ICONCLASS']:'empty')+'"></div></td>\n'+
					'			<td class="item'+(items[i]['DISABLED'] == true? ' disabled' : '')+(items[i]['DEFAULT'] == true? ' default' : '')+'"'+(items[i]["TITLE"]? ' title="'+items[i]["TITLE"]+'"' : '')+'>'+items[i]['TEXT']+'</td>\n'+
					'		</tr>\n'+
					'	</table>\n';
			}
		}

		div.style.width = tbl.parentNode.offsetWidth;
	}
	
	this.IsVisible = function()
	{
		return (document.getElementById(this.menu_id).style.visibility != 'hidden');
	}

	this.ShowMenu = function(control, items, bFixed)
	{
		if(this.controlDiv == control)
			this.PopupHide();
		else
		{
			this.PopupHide();
			if(items)
				this.BuildItems(items);

			control.className += ' pressed';
			var pos = jsUtils.GetRealPos(control);
			pos["bottom"]+=2;

			if(bFixed == true && !jsUtils.IsIE())
			{
				pos["top"] += document.body.scrollTop;
				pos["bottom"] += document.body.scrollTop;
				pos["left"] += document.body.scrollLeft;
				pos["right"] += document.body.scrollLeft;
			}

			this.controlDiv = control;
			this.OnClose = function()
			{
				control.className = control.className.replace(/\s*pressed/ig, "");
			}
			this.PopupShow(pos);
		}
	}
}
