function writeFlashHTML2( arg )
{
  var parm = []
  var _default_version = "8,0,24,0";
  var _default_quality = "high";
  var _default_align = "middle";
  var _default_menu = "false";

  for(i = 0; i < arguments.length; i ++)
  {
    parm[i] = arguments[i].split(' ').join('').split('=')
      for (var j = parm[i].length-1; j > 1; j --){
        parm[i][j-1]+="="+parm[i].pop();
      }
    switch (parm[i][0])
    {
      case '_version'   : var _version = parm[i][1] ; break ; 
      case '_swf'       : var _swf     = parm[i][1] ; break ; 
      case '_base'      : var _base    = parm[i][1] ; break ; 
      case '_quality'   : var _quality = parm[i][1] ; break ; 
      case '_loop'      : var _loop    = parm[i][1] ; break ; 
      case '_bgcolor'   : var _bgcolor = parm[i][1] ; break ; 
      case '_wmode'     : var _wmode   = parm[i][1] ; break ; 
      case '_play'      : var _play    = parm[i][1] ; break ; 
      case '_menu'      : var _menu    = parm[i][1] ; break ; 
      case '_scale'     : var _scale   = parm[i][1] ; break ; 
      case '_salign'    : var _salign  = parm[i][1] ; break ; 
      case '_height'    : var _height  = parm[i][1] ; break ; 
      case '_width'     : var _width   = parm[i][1] ; break ; 
      case '_hspace'    : var _hspace  = parm[i][1] ; break ; 
      case '_vspace'    : var _vspace  = parm[i][1] ; break ; 
      case '_align'     : var _align   = parm[i][1] ; break ; 
      case '_class'     : var _class   = parm[i][1] ; break ; 
      case '_id'        : var _id      = parm[i][1] ; break ; 
      case '_name'      : var _name    = parm[i][1] ; break ; 
      case '_style'     : var _style   = parm[i][1] ; break ; 
      case '_declare'   : var _declare = parm[i][1] ; break ; 
      case '_flashvars' : var _flashvars = parm[i][1] ; break ; 
      default           :;
    }
  }

  var thtml = ""
  thtml += "<object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=" + ((_version)?_version:_default_version) + "'"
  if(_width)        thtml += " width='" + _width + "'"
  if(_height)       thtml += " height='" + _height + "'"
  if(_hspace)       thtml += " hspace='" + _hspace + "'"
  if(_vspace)       thtml += " vspace='" + _vspace + "'"
  if(_align)        thtml += " align='" + _align + "'"
  else              thtml += " align='" + _default_align + "'"
  if(_class)        thtml += " class='" + _class + "'"
  if(_id)           thtml += " id='" + _id + "'"
  if(_name)         thtml += " name='" + _name + "'"
  if(_style)        thtml += " style='" + _style + "'"
  if(_declare)      thtml += " " + _declare
                    thtml += ">"
  if(_swf)          thtml += "<param name='movie' value='" + _swf + "'>"
  if(_quality)      thtml += "<param name='quality' value='" + _quality + "'>" 
  else              thtml += "<param name='quality' value ='" + _default_quality + "'>"
  if(_loop)         thtml += "<param name='loop' value='" + _loop + "'>"
  if(_bgcolor)      thtml += "<param name='bgcolor' value='" + _bgcolor + "'>"
  if(_play)         thtml += "<param name='play' value='" + _play + "'>"
  if(_menu)         thtml += "<param name='menu' value='" + _menu + "'>"
  else              thtml += "<param name='menu' value='" + _default_menu + "'>"
  if(_scale)        thtml += "<param name='scale' value='" + _scale + "'>"
  if(_salign)       thtml += "<param name='salign' value='" + _salign + "'>"
  if(_wmode)        thtml += "<param name='wmode' value='" + _wmode + "'>"
  if(_base)         thtml += "<param name='base' value='" + _base + "'>"
  if(_flashvars)    thtml += "<param name='flashvars' value='" + _flashvars  + "'>"
                    thtml += "<embed pluginspage='http://www.macromedia.com/go/getflashplayer'"
  if(_width)        thtml += " width='" + _width + "'"
  if(_height)       thtml += " height='" + _height + "'"
  if(_hspace)       thtml += " hspace='" + _hspace + "'"
  if(_vspace)       thtml += " vspace='" + _vspace + "'"
  if(_align)        thtml += " align='" + _align + "'"
  else              thtml += " align='" + _default_align + "'"
  if(_class)        thtml += " class='" + _class + "'"
  if(_id)           thtml += " id='" + _id + "'"
  if(_name)         thtml += " name='" + _name + "'"
  if(_style)        thtml += " style='" + _style + "'"
                    thtml += " type='application/x-shockwave-flash'"
  if(_declare)      thtml += " " + _declare  
  if(_swf)          thtml += " src='" + _swf + "'"
  if(_quality)      thtml += " quality='" + _quality + "'"
  else              thtml += " quality='" + _default_quality + "'"
  if(_loop)         thtml += " loop='" + _loop + "'"
  if(_bgcolor)      thtml += " bgcolor='" + _bgcolor + "'"
  if(_play)         thtml += " play='" + _play + "'"
  if(_menu)         thtml += " menu='" + _menu + "'"
  else              thtml += " menu='" + _default_menu + "'"
  if(_scale)        thtml += " scale='" + _scale + "'"
  if(_salign)       thtml += " salign='" + _salign + "'"
  if(_wmode)        thtml += " wmode='" + _wmode + "'"
  if(_base)         thtml += " base='" + _base + "'"
  if(_flashvars)    thtml += " flashvars='" + _flashvars + "'"
                    thtml += "></embed>"
                    thtml += "</object>"
  document.write(thtml)
}
