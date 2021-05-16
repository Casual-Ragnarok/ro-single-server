<?php
/**
 * DZAPP Haodai URL Rewrite Settings
 *
 * @copyright (c) 2013 DZAPP. (http://www.dzapp.cn)
 * @author BranchZero <branchzero@gmail.com>
 */


echo '<h1>'.lang('plugin/dzapp_haodai','rewrite_intro_title').'</h1>
<pre class="colorbox">
'.lang('plugin/dzapp_haodai','rewrite_intro_info').'
</pre>
<h1>Apache</h1>
<pre class="colorbox">
RewriteCond %{QUERY_STRING} ^(.*)$
RewriteRule ^haodai\.html$ plugin.php?id=dzapp_haodai&%1
RewriteCond %{QUERY_STRING} ^(.*)$
RewriteRule ^haodai-calculator-([a-z]+)\.html$ plugin.php?id=dzapp_haodai&action=calc&type=$1&%2
RewriteCond %{QUERY_STRING} ^(.*)$
RewriteRule ^haodai-(view|apply)-([0-9]+)-([a-z]+)-([0-9]+)-([0-9]+)\.html$ plugin.php?id=dzapp_haodai&action=$1&xd_id=$2&xd_type=$3&month=$4&money=$5&%6
RewriteCond %{QUERY_STRING} ^(.*)$
RewriteRule ^haodai-list-([a-z]+)-([0-9]+)\.html$ plugin.php?id=dzapp_haodai&action=list&type=$1&page=$2&%3
RewriteCond %{QUERY_STRING} ^(.*)$
RewriteRule ^haodai-news-([0-9]+)\.html$ plugin.php?id=dzapp_haodai&action=news&aid=$1&%2
</pre>
<h1>IIS6</h1>
<pre class="colorbox">
RewriteRule ^(.*)/haodai\.html(\?(.*))*$ $1/plugin\.php\?id=dzapp_haodai&$3
RewriteRule ^(.*)/haodai-calculator-([a-z]+)\.html(\?(.*))*$ $1/plugin\.php\?id=dzapp_haodai&action=calc&type=$2&$4
RewriteRule ^(.*)/haodai-(view|apply)-([0-9]+)-([a-z]+)-([0-9]+)-([0-9]+)\.html(\?(.*))*$ $1/plugin\.php\?id=dzapp_haodai&action=$2&xd_id=$3&xd_type=$4&month=$5&money=$6&$8
RewriteRule ^(.*)/haodai-list-([a-z]+)-([0-9]+)\.html(\?(.*))*$ $1/plugin\.php\?id=dzapp_haodai&action=list&type=$2&page=$3&$5
RewriteRule ^(.*)/haodai-news-([0-9]+)\.html(\?(.*))*$ $1/plugin\.php\?id=dzapp_haodai&action=news&aid=$2&$4
</pre>
<h1>IIS7</h1>
<pre class="colorbox">
&lt;rewrite&gt;
	&lt;rules&gt;
		&lt;rule name="haodai"&gt;
			&lt;match url="^(.*/)*haodai.html\?*(.*)$" /&gt;
			&lt;action type="Rewrite" url="{R:1}/plugin.php\?id=dzapp_haodai&amp;{R:2}" /&gt;
		&lt;/rule&gt;
		&lt;rule name="haodai_calculator"&gt;
			&lt;match url="^(.*/)*haodai-calculator-([a-z]+).html\?*(.*)$" /&gt;
			&lt;action type="Rewrite" url="{R:1}/plugin.php\?id=dzapp_haodai&amp;action=calc&amp;type={R:2}&amp;{R:3}" /&gt;
		&lt;/rule&gt;
		&lt;rule name="haodai_view_apply"&gt;
			&lt;match url="^(.*/)*haodai-(view|apply)-([0-9]+)-([a-z]+)-([0-9]+)-([0-9]+).html\?*(.*)$" /&gt;
			&lt;action type="Rewrite" url="{R:1}/plugin.php\?id=dzapp_haodai&amp;action={R:2}&amp;xd_id={R:3}&amp;xd_type={R:4}&amp;month={R:5}&amp;money={R:6}&amp;{R:7}" /&gt;
		&lt;/rule&gt;
		&lt;rule name="haodai_list"&gt;
			&lt;match url="^(.*/)*haodai-list-([a-z]+)-([0-9]+).html\?*(.*)$" /&gt;
			&lt;action type="Rewrite" url="{R:1}/plugin.php\?id=dzapp_haodai&amp;action=list&amp;type={R:2}&amp;page={R:3}&amp;{R:4}" /&gt;
		&lt;/rule&gt;
		&lt;rule name="haodai_news"&gt;
			&lt;match url="^(.*/)*haodai-news-([0-9]+).html\?*(.*)$" /&gt;
			&lt;action type="Rewrite" url="{R:1}/plugin.php\?id=dzapp_haodai&amp;action=news&amp;aid={R:2}&amp;{R:3}" /&gt;
		&lt;/rule&gt;
	&lt;/rules&gt;
&lt;/rewrite&gt;
</pre>
<h1>Nginx</h1>
<pre class="colorbox">
rewrite ^([^\.]*)/haodai\.html$ $1/plugin.php?id=dzapp_haodai last;
rewrite ^([^\.]*)/haodai-calculator-([a-z]+)\.html$ $1/plugin.php?id=dzapp_haodai&action=calc&type=$2 last;
rewrite ^([^\.]*)/haodai-(view|apply)-([0-9]+)-([a-z]+)-([0-9]+)-([0-9]+)\.html$ $1/plugin.php?id=dzapp_haodai&action=$2&xd_id=$3&xd_type=$4&month=$5&money=$6 last;
rewrite ^([^\.]*)/haodai-list-([a-z]+)-([0-9]+)\.html$ $1/plugin.php?id=dzapp_haodai&action=list&type=$2&page=$3 last;
rewrite ^([^\.]*)/haodai-news-([0-9]+)\.html$ $1/plugin.php?id=dzapp_haodai&action=news&aid=$2 last;
</pre>';
?>