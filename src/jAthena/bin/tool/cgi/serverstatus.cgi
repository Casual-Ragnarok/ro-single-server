#!/usr/bin/perl

#=========================================================================
# serverstatus.cgi  ver.1.01  by 胡蝶蘭
#	checkversionをラップした、サーバー状態を表示するcgi
#
# ** 設定方法 **
#
# - 下の$checkv変数にcheckversionへのパスを設定すること。
# - perlにパスが通っていない場合は $perl をperlへの正しいパスにすること。
# - 他は普通のCGIと同じ。（実行権やcgi-binフォルダなど）
# - %serversのipをそれぞれ正しく設定する。ホスト名可。現在はinterとcharは同じ
# - 複数map鯖にしている場合は,%serversに行を加えることで対応可能だが、
#   @serverorderに%serversのキーを加えることを忘れないように。
# - @state1,@state2で表示を多少変更可能。
#
# ** その他 **
# - あくまで現在の状態表示でログはとっていないので過去のデータは参照できない
# - キャッシュなどしていないのでアクセスされるたびにチェックする。
#   よってアクセスが多いと負荷が大きくなるので注意。
# - pingによるチェックはおまけ程度。tcp-pingは負荷が高い。
#	icmp-pingは負荷軽めだがroot権が必要なので実質無理。
#   Net::Ping必須。ActivePerlなどではalarmが未実装で動かない可能性あり。
#   うまくいかないならpingしないほうが負荷が低くなる。
#
#-------------------------------------------------------------------------


my($checkv)="../checkversion";	# checkversionのパス(おそらく変更が必要)
my($perl)="perl";				# perlのコマンド名

my($checkping)="tcp";			# NGのときpingによるチェックを行うpingの種類
								# "tcp","udp","icmp"(root権必要),""から選択
								# ""だとpingしない。
								# Net::Pingがない/実装されていないと無視

my(@serverorder)=(				# 表示順
	"login","char","inter","map"
);
my(%servers)=(					# データ(ipと名前)
	"login"	=> { "ip"=>"127.0.0.1:6900", "desc"=>"Login Server" },
	"char"	=> { "ip"=>"127.0.0.1:6121", "desc"=>"Charactor Server" },
	"inter"	=> { "ip"=>"127.0.0.1:6121", "desc"=>"Inter Server" },
	"map"	=> { "ip"=>"127.0.0.1:5121", "desc"=>"Map Server" },
);

my(@state1)=(					# 状態表示
	"Down",		# 接続できない
	"Good",		# 正常動作中
	"Error",	# %serversの設定がおかしい(ポート番号)
	"Closed",	# pingには応答
);
my(@state2)=(					# 色
	"#ffc0c0",	# 接続できない
	"#c0ffc0",	# 正常動作中
	"#c0c0ff",	# %serversの設定がおかしい(ポート番号)
	"#ffffc0",	# pingには応答
);

#--------------------------- 設定ここまで --------------------------------




use strict;
eval " use Net::Ping; ";


my($msg)=<<"EOD";
<html>
<head><title>Athena Server Status</title></head>
<body text="black" bgcolor="white" link="blue" vlink="blue" alink="blue">
<h1>Athena Server Status</h1>
<table border=1>
<tr><th>Server</th><th>Address</th><th>Status</th><th>Version</th></tr>
EOD

my(%langconv)=(
);

my($i);
foreach $i (@serverorder){
	my($state)=0;
	
	open PIPE,"$perl $checkv $servers{$i}->{ip} |"
		or HttpError("Can't execute checkversion.\n");
	my(@dat)=<PIPE>;
	close PIPE;
	
	if($dat[1]=~m/Athena/ && $dat[2]=~/server/){
		if($dat[2]=~/$i/ ){
			$state=1;
		}else{
			$state=2;
		}
	}elsif($checkping){
		eval { 
			$dat[1]="n/a";
			my($p) = Net::Ping->new($checkping);
			my($addr)=$servers{$i}->{ip};
			$addr=~s/\:\d+$//;
			$state=3 if $p->ping($addr);
			$p->close();
		};
	}
	
	$msg.= "<tr bgcolor=\"$state2[$state]\"><td>".$servers{$i}->{desc}.
		"</td><td>".$servers{$i}->{ip}."</td><td>$state1[$state]</td>".
		"<td>$dat[1]</td></tr>"
}
$msg.="</table></body></html>";

print "Content-type: text/html\n\n$msg";

sub LangConv {
	my(@lst)= @_;
	my($a,$b,@out)=();
	foreach $a(@lst){
		foreach $b(keys %langconv){
			$a=~s/$b/$langconv{$b}/g;
			my($rep1)=$1;
			$a=~s/\$1/$rep1/g;
		}
		push @out,$a;
	}
	return @out;
}

sub HttpMsg {
	my($msg)=join("", LangConv(@_));
	$msg=~s/\n/<br>\n/g;
	print LangConv("Content-type: text/html\n\n"),$msg;
	exit;
}

sub HttpError {
	my($msg)=join("", LangConv(@_));
	$msg=~s/\n/<br>\n/g;
	print LangConv("Content-type: text/html\n\n"),$msg;
	exit;
}


