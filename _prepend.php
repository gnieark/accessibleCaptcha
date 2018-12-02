<?php
/*
    Copyright 2009 Julien Wajsberg <felash@gmail.com>
    
    This file is part of dotclear-accessible-captcha.

    dotclear-accessible-captcha is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, version 3 of the License.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('DC_RC_PATH')) { return; }
 
global $__autoload, $core;
$__autoload['dcFilterAccessibleCaptcha'] = dirname(__FILE__).'/class.dc.filter.accessible.captcha.php';
$__autoload['AccessibleCaptcha'] = dirname(__FILE__).'/class.accessible.captcha.php';


$core->spamfilters[] = 'dcFilterAccessibleCaptcha';

$core->addBehavior('publicCommentFormAfterContent',
			array('dcFilterAccessibleCaptcha','publicCommentFormAfterContent'));

$core->addBehavior('exportFull',array('dcFilterAccessibleCaptcha','exportFull'));
$core->addBehavior('exportSingle',array('dcFilterAccessibleCaptcha','exportSingle'));
?>
