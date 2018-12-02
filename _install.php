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

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
// c'est le nom du répertoire
$moduleName = "accessibleCaptcha";

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo($moduleName, 'version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion($moduleName);
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
//echo $i_version . '<br>' . $m_version . '<br>';

if (version_compare($i_version, $m_version,'>=')) {
	return;
}

$s = new dbStruct($core->con,$core->prefix);
$s->captcha
    ->id('bigint', 0, false)
    ->question('varchar', 150, false)
    ->answer('varchar', 150, false)
    ->blog_id('varchar', 32, false)
    
    ->primary('pk_captcha', 'id')
    ->index('idx_captcha_blog_btree', 'btree', 'blog_id')
    ->reference('fk_captcha_blog', 'blog_id', 'blog', 'blog_id', 'cascade', 'cascade');
    
$s->captcha_hash
    ->id('bigint', 0, false)
    ->hash('varchar', 150, false)
    ->captcha_id('bigint', 0, false)
    ->timestamp('timestamp', 0, false)
    
    ->primary('pk_captcha_hash', 'id')
    ->index('idx_captcha_hash_btree', 'btree', 'hash')
    ->reference('fk_captcha_hash_captcha', 'captcha_id', 'captcha', 'id', 'cascade', 'cascade');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# La procédure d'installation commence vraiment là
$core->setVersion($moduleName, $m_version);
return true;

